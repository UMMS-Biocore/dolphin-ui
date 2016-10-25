"""
    This tool recursively map sequenced files to any number of index files in given order.
    usage: dophin_wrapper.py [options]
"""
# imports
import logging
import warnings
import MySQLdb
import os, re, string, sys, commands
from sys import argv, exit, stderr
from optparse import OptionParser
from os.path import dirname, exists, join
from os import system
import subprocess
from subprocess import Popen, PIPE
import json
import ConfigParser
import time

warnings.filterwarnings('ignore', '.*the sets module is deprecated.*',
         DeprecationWarning, 'MySQLdb')
        
from workflowdefs import *

class Dolphin:
    cmd = 'python %(dolphin_tools_dir)s/runWorkflow.py -f %(params_section)s -i %(input_fn)s -w %(workflow)s -p %(dolphin_default_params)s -u %(username)s -o %(outdir)s %(runidstr)s %(wkeystr)s'
    config = ConfigParser.ConfigParser()
    params_section = ''
    
    def __init__(self, params_section):
        self.params_section = params_section
        
    def runSQL(self, sql):
      try:
        db = MySQLdb.connect(
          host = self.config.get(self.params_section, "db_host"),
          user = self.config.get(self.params_section, "db_user"),
          passwd = self.config.get(self.params_section, "db_password"),
          db = self.config.get(self.params_section, "db_name"),
          port = int(self.config.get(self.params_section, "db_port")))

        cursor = db.cursor()
        cursor.execute(sql)
        #print sql
        results = cursor.fetchall()
        cursor.close()
        del cursor
        return results
        
      except Exception, ex:
        self.stop_err('Error (line:%s)running runSQL\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
      finally:
        db.commit()
        db.close()

    def updatePID(self, rpid, pid):
      try:
        sql = "UPDATE ngs_runparams SET wrapper_pid='%s',runworkflow_pid='%s' WHERE id='%s'"%(os.getpid(), pid, rpid)
        return self.runSQL(sql)
      except Exception, ex:
        self.stop_err('Error (line:%s)running updatePID\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
        
    def getRunParamsID(self, rpid):
      try:
         rpstr="";
         if (rpid > 0):
           rpstr=" AND nrp.id=%s"%str(rpid)
         sql = "SELECT DISTINCT nrl.run_id, u.username, nrp.barcode from ngs_runlist nrl, ngs_runparams nrp, users u where nrp.id=nrl.run_id and u.id=nrl.owner_id %s;"%rpstr
         return self.trySQL(sql, "getRunParamsID")
      except Exception, ex:
        self.stop_err('Error (line:%s)running getRunParamsID\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))

    def trySQL(self, sql, func):
      try:
        trials=0
        while trials<5:
           print trials
           ret = self.runSQL(sql)
           print "LEN:"+str(len(ret))

           if (len(ret)>0):
              return ret

           time.sleep(15)
           trials=trials+1 

        return ret
      except Exception, ex:
        self.stop_err('Error (line:%s)running trySQL\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
 
    def getRunParams(self, runparamsid):
      try:
        sql = "SELECT json_parameters from ngs_runparams where id='%d'"%runparamsid
        result = self.runSQL(sql)
        for row in result:
            #print row[0]
            return json.loads(row[0])
      except Exception, ex:
        self.stop_err('Error (line:%s)running getRunParams\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
    
    def getAmazonCredentials(self, username):
      try:
        sql = 'SELECT DISTINCT ac.* FROM amazon_credentials ac, group_amazon ga, users u where ac.id=ga.amazon_id and ga.group_id=u.group_id and u.username="'+username+'";'
        results = self.runSQL(sql)
    
        return results
      except Exception, ex:
        self.stop_err('Error (line:%s)running getAmazonCredentials\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
    
    def getDirs(self, runparamsid, isbarcode):
      try:
        tablename="ngs_fastq_files"
        fields="d.backup_dir fastq_dir, d.backup_dir, d.amazon_bucket, rp.outdir"
        idmatch="s.id=tl.sample_id"
        sql = "SELECT DISTINCT %(fields)s FROM ngs_runlist nr, ngs_samples s, %(tablename)s tl, ngs_dirs d, ngs_runparams rp where nr.sample_id=s.id and %(idmatch)s and d.id=tl.dir_id and rp.id=nr.run_id and nr.run_id='"+str(runparamsid)+"';"
        results=self.runSQL(sql%locals())
        if (results==() or self.checkIfAnewSampleAdded(runparamsid)):
           fields="d.fastq_dir, d.backup_dir, d.amazon_bucket, rp.outdir"
           if (isbarcode):
               idmatch="s.lane_id=tl.lane_id"
               tablename="ngs_temp_lane_files"
           else:
               tablename="ngs_temp_sample_files"
           print sql%locals() 
           results=self.runSQL(sql%locals())
        return results[0]
      except Exception, ex:
        self.stop_err('Error (line:%s)running getDirs\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
   
    def checkIfAnewSampleAdded(self, runparamsid):
      try:
        sql = "SELECT a.sample_id FROM (SELECT nr.sample_id FROM ngs_runlist nr, ngs_temp_sample_files ts where nr.sample_id=ts.sample_id and run_id="+str(runparamsid)+") a where sample_id NOT IN(SELECT nr.sample_id FROM ngs_runlist nr, ngs_fastq_files ts where nr.sample_id=ts.sample_id and run_id="+str(runparamsid)+")"
        sampleids=self.runSQL(sql%locals())
        if (sampleids != ()):
            return 1
        return 0
      except Exception, ex:
        self.stop_err('Error (line:%s)running checkIfAnewSampleAdded\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
 
    def getSampleList(self, runparamsid):
      try:
        tablename="ngs_fastq_files"
        dirfield="d.backup_dir"
        sql = "SELECT s.samplename, %(dirfield)s dir, ts.file_name FROM ngs_runparams nrp, ngs_runlist nr, ngs_samples s, %(tablename)s ts, ngs_dirs d where nr.run_id=nrp.id and nr.sample_id=s.id and s.id=ts.sample_id and d.id=ts.dir_id and nr.run_id='"+str(runparamsid)+"';"
        samplelist=self.runSQL(sql%locals())
        if (samplelist==() or self.checkIfAnewSampleAdded(runparamsid)):
            dirfield="d.fastq_dir"
            tablename="ngs_temp_sample_files"
            samplelist=self.runSQL(sql%locals())
        return self.getInputParams(samplelist)
      except Exception, ex:
        self.stop_err('Error (line:%s)running getSampleList\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
    
    def getInputParams(self, samplelist):
      try:
        inputparams=""
        for row in samplelist:
            if (inputparams):
               inputparams=inputparams+":"
            content = row[2]
            content = content.replace( ',', ","+row[1]+"/" )
            inputparams=inputparams+row[0]+","+row[1]+"/"+content
        spaired=None
        if (',' in row[2]):
            spaired="paired"
        return (spaired, inputparams)
      except Exception, ex:
        self.stop_err('Error (line:%s)running getInputParams\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
    
    def getLaneList(self, runparamsid):
      try:
        tablename="ngs_fastq_files"
        fields='d.backup_dir dir, tl.file_name'
        sql = "SELECT DISTINCT %(fields)s FROM ngs_runlist nrl, ngs_dirs d, ngs_runparams nrp, ngs_samples s, %(tablename)s tl where nrl.run_id=nrp.id and d.id=tl.dir_id and s.lane_id = tl.lane_id and s.id=nrl.sample_id and nrp.id='"+str(runparamsid)+"';"
        result=self.runSQL(sql%locals())
        if (not result):
            tablename="ngs_temp_lane_files"
            fields='d.fastq_dir dir, tl.file_name'
            print sql%locals()
            result=self.runSQL(sql%locals())
    
        inputparams=""
        for row in result:
            if (inputparams):
                inputparams=inputparams+":"
            content = row[1]
            content = content.replace( ',', ","+row[0]+"/" )
            inputparams=inputparams+row[0]+"/"+content
        spaired=None
        if (',' in row[1]):
            spaired="paired"
    
        fields='s.samplename, s.barcode'
        result=self.runSQL(sql%locals())
        if (not result):
            tablename="ngs_fastq_files"
            result=self.runSQL(sql%locals())
        barcode="" 
        for row in result:
            barcode=barcode+":"+row[0]+","+row[1]
        return (spaired, inputparams, barcode)
      except Exception, ex:
        self.stop_err('Error (line:%s)running getLaneList\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))

    def writeInputParamLine(self, fp, jsonobj, input_str, input_object, itself, previous_str=None):
      try:
       previous = ( previous_str if previous_str!=None else "NONE" )
       if (input_object in jsonobj and jsonobj[input_object].lower() != 'none' and jsonobj[input_object]!=''):
         print >>fp, '%s=%s'%(input_str, self.parse_content(jsonobj[input_object]))
         if (previous_str):
            print >>fp, '%s=%s'%("@PREVIOUS"+itself, previous_str)
         previous=itself
       else:
         print >>fp, '%s=NONE'%(input_str)
       return previous
      except Exception, ex:
        self.stop_err('Error (line:%s)running writeInputParamLine\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
       
         
    def writeInput(self,  input_fn, data_dir, content, runparams, barcodes) :
      try:
       commonind=''
       if ('commonind' in runparams and runparams['commonind'].lower() != "none"):
          commonind = re.sub('test', '', runparams['commonind'])
          gb=runparams['genomebuild'].split(',')
          commonind = re.sub('genome', str(gb[1]), commonind)
       print 'COMMONIND:'+commonind       
       gb=runparams['genomebuild'].split(',')
       previous="NONE"
       fp = open( input_fn, 'w' )
       print >>fp, '@CONFIG=%s'%self.params_section
       print >>fp, '@GENOME=%s'%gb[0]

       gb[1] = re.sub('test', '', gb[1])
       genomeindex=gb[1]
    
       print >>fp, '@VERSION=%s'%gb[1]
       
       print >>fp, '@INPUT=%s'%content
       print >>fp, '@DATADIR=%s'%data_dir
       print >>fp, '@OUTFILE=%s'%input_fn
       print >>fp, '@GENOMEBUILD=%s,%s'%(gb[0],gb[1])
       print >>fp, '@SPAIRED=%s'%runparams['spaired']
       previous="NONE" 
       #u'barcodes': [{u'distance': u'1', u'format': u'5 end read 1'}]
       if ('barcodes' in runparams and barcodes):
          pipe=runparams['barcodes'][0]
          barcode="Distance,%s:Format,%s%s"%(str(int(pipe['distance'])+1), str(pipe['format']), barcodes)
          print >>fp, '@BARCODES=%s'%barcode
          previous="BARCODES"
       else:
          print >>fp, '@BARCODES=NONE'
       previous=self.writeInputParamLine(fp, runparams, "@ADAPTER", 'adapters', "ADAPTER", previous )
       
       if ( 'quality' in runparams and type(runparams['quality']) is list):
            pipe=runparams['quality'][0]
            runparams['quality']="%s:%s:%s:%s:%s"%(pipe['windowSize'],pipe['requiredQuality'],pipe['leading'],pipe['trailing'],pipe['minlen'])
       
       previous=self.writeInputParamLine(fp, runparams, "@QUALITY", 'quality', "QUALITY", previous)
       
       if ( 'trim' in runparams and type(runparams['trim']) is list):
          pipe=runparams['trim'][0]
          if (pipe["5len1"]>0 and pipe["3len1"]>0):
            self.writeInputParamLine(fp, pipe, "@TRIMPAIRED", 'trimpaired', "TRIM")
            if ('trimpaired' in pipe and pipe['trimpaired']=="paired"):
               if (pipe["5len2"]>0 and pipe["3len2"]>0):
                  runparams['trim']="%s:%s:%s:%s"%(pipe["5len1"],pipe["3len1"],pipe["5len2"],pipe["3len2"])
            else:
               runparams['trim']="%s:%s"%(str(pipe["5len1"]),str(pipe["3len1"]))
       previous=self.writeInputParamLine(fp, runparams, "@TRIM", 'trim', "TRIM", previous)
       
       if ('commonind' in runparams and  str(runparams['commonind']).lower() != "none"):
         arr=re.split(r'[,:]+', self.parse_content(commonind))
         for i in arr:
           print i
           if(len(i)>1):
              default_bowtie_params="@DEFBOWTIE2PARAM"
              default_description="@DEFDESCRIPTION"
           print >>fp, '@PARAM%s=@GCOMMONDB/%s/%s,%s,%s,%s,1,%s'%(i,i,i,i,default_bowtie_params,default_description,previous)
           if (i != "ucsc" and i != gb[1]):
              previous=i
    
         print >>fp, '@ADVPARAMS=' + ('%s'%(self.parse_content(runparams['advparams'])) if ('advparams' in runparams) else 'NONE')
    
       mapnames = (runparams['commonind'] if ('commonind' in runparams and runparams['commonind'].lower()!="none") else "")
   
       if ('custominds' in runparams and str(runparams['custominds']).lower() != "none"):
          print str(runparams['custominds'])
          for vals in runparams['custominds']:
            index=self.parse_content(vals['FullPath'] + "/" + vals['IndexPrefix'])
            name=self.parse_content(self.replace_space(vals['IndexPrefix']))
            
            mapnames = (mapnames + "," + name + ":" + index if mapnames!="" else name + ":" + index)
            
            bowtie_params= self.replace_space(self.convert_comma(vals['BowtieParams']))
            description=self.parse_content(self.replace_space(vals['Description']))
            filter_out=vals['Filter Out']
    
            print >>fp, '@PARAM%s=%s,%s,%s,%s,%s,%s'%(name,index,name,bowtie_params,description,filter_out,previous)
            if (str(filter_out)=="yes"):
               previous=name
       previoussplit=previous
       previous=self.writeInputParamLine(fp, runparams, "@SPLIT", 'split', "SPLIT", previous )
              
       if ('pipeline' in runparams):
           for pipe in runparams['pipeline']:
             if (pipe['Type']=="RNASeqRSEM"):
               paramsrsem=pipe['Params'] if ('Params' in pipe and pipe['Params']!="") else "NONE"
               print >>fp, '@PARAMSRSEM=%s'%(self.parse_content(paramsrsem))
               print >>fp, '@TSIZE=50';
               print >>fp, '@PREVIOUSRSEM=%s'%(previoussplit)
               if ("MarkDuplicates" in pipe and pipe['MarkDuplicates'].lower()=="yes"):
                   paired_str=""
                   if (runparams['spaired'] == "paired"):
                       paired_str = " --no-mixed --no-discordant " 
                   bowtie_params=self.replace_space(self.convert_comma("-N 1 --sensitive --dpad 0 --gbar 99999999 --mp 1,1 --np 1 --score-min L,0,-0.1 " + paired_str))
                   filter_out="0"
                   name="RSEMBAM"
                   if ('CustomGenomeIndex' in pipe and pipe['CustomGenomeIndex'].lower()!="none"): 
                       indexsuffix = pipe['CustomGenomeIndex'] + ".transcripts"
                       indexname = os.path.basename(indexsuffix)
                       name = indexname
                   else:
                       indexname="rsem_ref.transcripts"
                       indexsuffix = "@GDB/%s"%(indexname) 
                   print >>fp, '@PARAM%s=%s,%s,%s,%s,%s,%s'%(name, indexsuffix,indexname,bowtie_params,indexname,filter_out,previous)
                   
             if (pipe['Type']=="Tophat"):
               paramstophat=pipe['Params'] if ('Params' in pipe and pipe['Params']!="") else "NONE"
               print >>fp, '@TSIZE=50';
               print >>fp, '@PARAMSTOPHAT=%s'%(self.parse_content(paramstophat))
               
             
             if (pipe['Type']=="DESeq"):
               name = ( pipe['Name'] if ('Name' in pipe) else  "")
               print >>fp, '@COLS%s=%s'%(name, self.remove_space(pipe['Columns']))
               print >>fp, '@CONDS%s=%s'%(name, self.remove_space(pipe['Conditions']))
               print >>fp, '@FITTYPE%s=%s'%(name, pipe['FitType'])
               print >>fp, '@HEATMAP%s=%s'%(name, pipe['HeatMap'])
               print >>fp, '@PADJ%s=%s'%(name, pipe['padj'])
               print >>fp, '@FOLDCHANGE%s=%s'%(name, pipe['foldChange'])
               print >>fp, '@DATASET%s=%s'%(name, pipe['DataType'])
    
             if (pipe['Type']=="ChipSeq"):
               chipinput=self.chip_parse_input(pipe['ChipInput'])
               bowtie_params=self.remove_space("-k_%s"%(str(pipe['MultiMapper'])))
               description="Chip_Mapping"
               filter_out="0"
               print >>fp, '@ADVPARAMS=NONE'
               print >>fp, '@CHIPINPUT=%s'%(chipinput)
               print >>fp, '@PARAMChip=@GCOMMONDB/%s/%s,Chip,%s,%s,%s,%s'%(gb[1],gb[1],bowtie_params,description,filter_out,previous)
               print >>fp, '@GENOMEINDEX=%s'%(genomeindex)
               print >>fp, '@TSIZE=%s'%(self.remove_space(str(pipe['TagSize'])))
               print >>fp, '@BWIDTH=%s'%(self.remove_space(str(pipe['BandWith'])))
               print >>fp, '@GSIZE=%s'%(self.remove_space(str(pipe['EffectiveGenome'])))
               print >>fp, '@EXTRAPARAMS=%s'%(pipe['Params'])

             if (pipe['Type']=="BisulphiteMapping"):
               if ('BSMapStep' in pipe and pipe['BSMapStep'] == "yes"):
                 print >>fp, '@DIGESTION=%s'%(  str(pipe['Digestion']) if ('Digestion' in pipe) else 'NONE' )
                 self.writeInputParamLine(fp, pipe, "@BSMAPPARAM", 'BSMapParams', "BSMapStep")
               if ('MCallStep' in pipe and pipe['MCallStep']== "yes"):
                 self.writeInputParamLine(fp, pipe, "@MCALLPARAM", 'MCallParams', "MCallStep")
               if ('MethylKit' in pipe and pipe['MethylKit']== "yes"):
                 print >>fp, '@TILE_SIZE=%s'%(pipe['TileSize'])
                 print >>fp, '@STEP_SIZE=%s'%(pipe['StepSize'])
                 print >>fp, '@STRAND=%s'%(pipe['StrandSpecific'])
                 print >>fp, '@TOPN=%s'%(pipe['TopN'])
                 print >>fp, '@MINCOVERAGE=%s'%(pipe['MinCoverage'])
                 print >>fp, '@GBUILD=%s'%(gb[1])
                 
             if (pipe['Type']=="DiffMeth"):
               name = ( pipe['Name'] if ('Name' in pipe) else  "")
               print >>fp, '@COLS%s=%s'%(name, self.remove_space(pipe['Columns']))
               print >>fp, '@CONDS%s=%s'%(name, self.remove_space(pipe['Conditions']))
               

             if (pipe['Type']=="HaplotypeCaller"):
               print >>fp, '@SMCTFC=%s'%(pipe['standard_min_confidence_threshold_for_calling'])
               print >>fp, '@SMCTFE=%s'%(pipe['standard_min_confidence_threshold_for_emitting'])
               print >>fp, '@MBQS=%s'%(pipe['min_base_quality_score'])
               print >>fp, '@MRPAS=%s'%(pipe['minReadsPerAlignmentStart'])
               print >>fp, '@MRIRPS=%s'%(pipe['maxReadsInRegionPerSample'])
               if ('common' in pipe and pipe['common'] == "yes"):
                 print >>fp, '@COMMON=%s'%(pipe['common'])
               if ('clinical' in pipe and pipe['clinical'] == "yes"):
                 print >>fp, '@CLINICAL=%s'%(pipe['clinical'])
               if ('enhancers' in pipe and pipe['enhancers'] == "yes"):
                 print >>fp, '@ENHANCER=%s'%(pipe['enhancers'])
               if ('promoters' in pipe and pipe['promoters'] == "yes"):
                 print >>fp, '@PROMOTER=%s'%(pipe['promoters'])
               if ('motifs' in pipe and pipe['motifs'] != "none"):
                 print >>fp, '@MOTIFS=%s'%(pipe['motifs'])
               if ('merge' in pipe and pipe['merge'] != "none"):
                 print >>fp, '@MERGEALL=%s'%(pipe['merge'])
               if ('peaks' in pipe and pipe['peaks'] != "none"):
                 print >>fp, '@PEAKS=%s'%(pipe['peaks'])
               if ('custombed' in pipe and pipe['custombed'] != "none"):
                 print >>fp, '@CUSTOMBED=%s'%(pipe['custombed'])

       print >>fp, '@MAPNAMES=%s'%(mapnames)
       print >>fp, '@PREVIOUSPIPE=%s'%(previous)
       
       fp.close()
      except Exception, ex:
        self.stop_err('Error (line:%s)running writeInput\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
    
    def prf(self, fp, text):
        print "HERE:"+str(text)
        if (str!=None and str(text).lower()!="none"):
           print >>fp, text
           
    def writeVisualizationStr(self, fp, type, pipe, sep):
      try:
        print pipe
        if ('IGVTDF' in pipe and pipe['IGVTDF'].lower()=="yes"):
            paramExtFactor = ( " -e " + str(pipe['ExtFactor']) if ('ExtFactor' in pipe and pipe['ExtFactor'] > 1) else '')
            self.prf( fp, stepIGVTDF % locals() )
        if ('BAM2BW' in pipe and pipe['BAM2BW'].lower()=="yes"):
            self.prf( fp, stepBam2BW % locals() )
            
      except Exception, ex:
        self.stop_err('Error (line:%s)running writeVisualizationStr\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
    
    def writeRSeQC ( self, fp, type, pipe, sep):
      try:
        if ('RSeQC' in pipe and pipe['RSeQC'].lower()=="yes" and type.lower().find("chip")<0):
            self.prf( fp, stepRSEQC % locals() )
            self.prf( fp, stepMergeRSEQC % locals() )
      except Exception, ex:
        self.stop_err('Error (line:%s)running writeRSeQC\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
            
    def writePicard (self, fp, type, pipe, sep):
      initialtype=type
      try:
        metrics = ("MarkDuplicates", "CollectRnaSeqMetrics", "CollectMultipleMetrics") 
        for metric in metrics:
          if( (metric=="CollectRnaSeqMetrics" and (str(type).lower().find("tophat")>1 or str(type).lower().find("rsem")>1  )) or metric != "CollectRnaSeqMetrics" ):
            self.prf( fp, stepPicard % locals() if ((metric in pipe and pipe[metric].lower()=="yes")) else None )
            if ("MarkDuplicates" in pipe and pipe['MarkDuplicates'].lower()=="yes"):
                type = "dedup"+initialtype
        
        if (('CollectRnaSeqMetrics' in pipe and pipe['CollectRnaSeqMetrics'].lower()=="yes") or ('CollectMultipleMetrics' in pipe and pipe['CollectMultipleMetrics'].lower()=="yes")):
            self.prf( fp, stepMergePicard % locals())
      except Exception, ex:
        self.stop_err('Error (line:%s)running writePicardWorkflow\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))

    def writeDedupForRSEM(self, pipe, runparams, type, fp, sep):
      try:
        type = "rsem_ref.transcripts"
        if ('CustomGenomeIndex' in pipe and pipe['CustomGenomeIndex'].lower()!="none"): 
            indexsuffix = pipe['CustomGenomeIndex'] + ".transcripts"
            indexname = os.path.basename(indexsuffix)
            type = indexname
        else:
            indexname = "RSEMBAM"

        self.prf( fp, stepSeqMapping % locals() )
        if ('split' in runparams and runparams['split'].lower() != 'none'):
            self.prf( fp, '%s'%(stepMergeBAM % locals()) )
            type = "merge" + type
        self.writePicard (fp, type, pipe, sep )
        return type
      except Exception, ex:
        self.stop_err('Error (line:%s)running dedupForRSEM\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))

    def writeWorkflow(self,  file, gettotalreads, amazonupload, backupS3, runparamsid, runparams ):
      try:
        commonind=''
        if ('commonind' in runparams and  runparams['commonind'].lower() != "none"):
          commonind = re.sub('test', '', runparams['commonind'])
          gb=runparams['genomebuild'].split(',')
          commonind = re.sub('genome', str(gb[1]), commonind)
        fp = open ( file, 'w')
        sep='\t'          
        resume = (runparams['resume'] if ('resume' in runparams) else "yes")
        self.prf(fp, stepDownload % locals() if ('submission' in runparams and str(runparams['submission']).lower()=="2") else None )
        self.prf(fp, stepCheck % locals() )
        self.prf(fp, stepBarcode % locals() if ('barcodes' in runparams and str(runparams['barcodes']).lower()!="none") else None )
        self.prf(fp, stepGetTotalReads % locals() if (gettotalreads and gettotalreads.lower()!="none") else None )
        self.prf(fp, stepBackupS3 % locals() if (backupS3 and backupS3.lower()!="none") else None )
        self.prf(fp, stepFastQC % locals() + "\n" + stepMergeFastQC % locals() if ('fastqc' in runparams and runparams['fastqc'].lower()=="yes") else None )
        self.prf(fp, stepAdapter % locals() if ('adapters' in runparams and runparams['adapters'].lower()!="none") else None )
        self.prf(fp, stepQuality % locals() if ('quality' in runparams and runparams['quality'].lower()!="none") else None )
        self.prf(fp, stepTrim % locals() if ('trim' in runparams and runparams['trim'].lower()!="none") else None  )
       
        countstep = False
        if ('commonind' in runparams and str(runparams['commonind']).lower() != 'none'):
           arr=re.split(r'[,:]+', self.parse_content(commonind))
           for i in arr:
              countstep = True
              if(len(i)>1):
                indexname=i
              self.prf(fp, stepSeqMapping % locals() )
        
        if ('custominds' in runparams and str(runparams['custominds']).lower() != 'none'):
           for vals in runparams['custominds']:
              countstep = True
              indexname = self.parse_content(self.replace_space(vals['IndexPrefix']))
              self.prf( fp, stepSeqMapping % locals() )
       
        if (countstep):
           self.prf( fp, stepCounts % locals() )
        if ('split' in runparams and runparams['split'].lower() != 'none'):
           thenumberofreads=str(runparams['split'])
           self.prf( fp, stepSplit % locals() )

        if ('pipeline' in runparams):
           for pipe in runparams['pipeline']:
              if (pipe['Type']=="RNASeqRSEM"):
                 dedup=False
                 genome_bam="yes"
                 bamsupport="no"
                 type="rsem"
                 previousrsem = "@PREVIOUSRSEM"

                 if('MarkDuplicates' in pipe and pipe['MarkDuplicates'].lower()=='yes'):
                     genome_bam="no"
                     bamsupport="yes"
                     type = self.writeDedupForRSEM(pipe, runparams, type, fp, sep)
                     previousrsem = "dedup" + type
                     type=previousrsem
                     self.prf( fp, stepPCRDups % locals())
                     
                 rsemref = (pipe['CustomGenomeIndex'] if ('CustomGenomeIndex' in pipe and pipe['CustomGenomeIndex'].lower()!="none") else "@RSEMREF" )

                 self.prf( fp, stepRSEM % locals() )
                 gis = ("genes","isoforms")
                 tes = ("expected_count", "tpm") 

                 for g_i in gis:
                   for t_e in tes:
                     self.prf( fp, stepRSEMCount % locals() )
                 self.writeVisualizationStr( fp, type, pipe, sep )
                 self.writeRSeQC ( fp, type, pipe, sep )
                 self.prf( fp, stepAlignmentCount % locals() )
              
              if (pipe['Type']=="Tophat"):
                 gtf = (pipe['CustomGenomeAnnotation'] if ('CustomGenomeAnnotation' in pipe and pipe['CustomGenomeAnnotation'].lower()!="none") else "@GTF" )
                 bowtie2index = (pipe['CustomGenomeIndex'] if ('CustomGenomeIndex' in pipe and pipe['CustomGenomeIndex'].lower()!="none") else "@BOWTIE2INDEX" )
                 self.prf( fp, stepTophat % locals() )
                 type="tophat"
                 if ('split' in runparams and runparams['split'].lower() != 'none'):
                    self.prf( fp, '%s'%(stepMergeBAM % locals()) )
                    type="mergetophat"
                 self.writePicard (fp, type, pipe, sep )
                 if ("MarkDuplicates" in pipe and pipe['MarkDuplicates'].lower()=="yes"):
                    type="dedup"+type
                    self.prf( fp, stepPCRDups % locals())
                 self.writeVisualizationStr( fp, type, pipe, sep )
                 self.writeRSeQC ( fp, type, pipe, sep )
                 self.prf( fp, stepAlignmentCount % locals() )

              if (pipe['Type'] == "DESeq"):
                 deseq_name =( pipe['Name'] if ('Name' in pipe) else '' )
                 self.prf( fp, '%s'%(stepDESeq2 % locals()) )

              if (pipe['Type'] == "ChipSeq"):
                 #Arrange ChipSeq mapping step
                 indexname='Chip'
                 self.prf( fp, '%s'%(stepSeqMapping % locals()) )
                 type="chip"
                 if ('split' in runparams and runparams['split'].lower() != 'none'):
                     self.prf( fp, '%s'%(stepMergeBAM % locals()) )
                     type="mergechip"
                 self.writePicard (fp, type, pipe, sep )
                 if ("MarkDuplicates" in pipe and pipe['MarkDuplicates'].lower()=="yes"):
                    type="dedup"+type
                    self.prf( fp, stepPCRDups % locals())

                 self.writeVisualizationStr( fp, type, pipe, sep )
                                  
                 #Set running macs step
                 self.prf( fp, '%s'%(stepMACS % locals()) )
                 self.prf( fp, '%s'%(stepAggregation % locals()) )
                 self.prf( fp, stepAlignmentCount % locals() )

              if (pipe['Type'] == "BisulphiteMapping"):
                 self.prf( fp, '%s'% ( stepBSMap % locals() if ('BSMapStep' in pipe and pipe['BSMapStep'].lower()=="yes") else None ) )
                 
                 type="bsmap"
                 if ('split' in runparams and runparams['split'].lower() != 'none'):
                    self.prf( fp, '%s'%(stepMergeBAM % locals()) )
                    type="mergebsmap"
                    
                 self.writePicard (fp, type, pipe, sep )
                 if ("MarkDuplicates" in pipe and pipe['MarkDuplicates'].lower()=="yes"):
                    type="dedup"+type 
                 
                 self.writeVisualizationStr( fp, type, pipe, sep )
                 if ('MCallStep' in pipe and pipe['MCallStep'].lower() == "yes"):    
                     self.prf( fp, '%s'% ( stepMCall % locals() if ('MCallStep' in pipe and pipe['MCallStep'].lower()=="yes") else None ) )
                 if ('MethylKit' in pipe and pipe['MethylKit'].lower() == "yes"): 
                     self.prf( fp, '%s'%(stepMethylKit % locals()) )   
             
              if (pipe['Type'] == "DiffMeth"):
                 diffmeth_name =( pipe['Name'] if ('Name' in pipe) else '' )
                 self.prf( fp, '%s'%(stepDiffMeth % locals()) )             

              if (pipe['Type'] == "HaplotypeCaller"):
                self.prf( fp, '%s'%(stepHaplotype % locals()) )
                type="haplotypecaller"
                
              if (pipe['Type']=="RNASeqRSEM"):
                #Arrange ChipSeq mapping step
                 indexname='Chip'
                 self.prf( fp, '%s'%(stepSeqMapping % locals()) )
                 type="atac"
                 if ('split' in runparams and runparams['split'].lower() != 'none'):
                     self.prf( fp, '%s'%(stepMergeBAM % locals()) )
                     type="merge"+type
                 self.writePicard (fp, type, pipe, sep )
                 if ("MarkDuplicates" in pipe and pipe['MarkDuplicates'].lower()=="yes"):
                    type="dedup"+type
                    self.prf( fp, stepPCRDups % locals())
                 self.writeVisualizationStr( fp, type, pipe, sep )
                                  
                 #Set running macs step
                 self.prf( fp, '%s'%(stepMACS % locals()) )
                 self.prf( fp, '%s'%(stepAggregation % locals()) )
                 self.prf( fp, stepAlignmentCount % locals() )

        level = str(1 if ('clean' in runparams and runparams['clean'].lower() != 'none') else 0)
        if(backupS3 == None):
            print >>fp, '%s'%(stepSummary % locals())
        print >>fp, '%s'%(stepClean % locals())

        fp.close()
      except Exception, ex:
        self.stop_err('Error (line:%s)running writeWorkflow\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))


    def replace_space(self, content) :
        content = re.sub('[\s\t,]+', '_', content)
        return content
        
    def remove_space(self, content) :
        content = content.replace( '__cr____cn__', '' )
        content = re.sub('[\s\t\n]+', '', content)
        return content
    def convert_comma(self, content) :
        content = content.replace(',', '__tc__')
        return content
        
    def parse_content(self, content, ncols=8, base64=False, verbose=0 ) :
        '''
        This is a function that parse the inputparam content and
        returns the base64 encoded string if base64 is True otherwise
        returns the concatenated string with by the conversion to make
        ('\t' -> ',', ' ' -> ',', '\n'->':').
        '''
        
        content = content.replace( '__tc__', ',' )
        content = content.replace( '__at__', '@' )
        content = content.replace( '__pd__', '' )
        content = content.replace( '__cr____cn__', ':' )
        content = re.sub('[\s\t,]+', ',', content)
        content = re.sub('[\n\r:]+', ':', content)
        content = re.sub(':+', ':', content)
        content = re.sub(':$', '', content)
        #content = re.sub('[-]+', '_', content)
        return content

    def chip_parse_input(self, content, ncols=8, base64=False, verbose=0):
        new_content = ""
        try:
            for input in content:
               new_content += input['name'] + '__tt__'
               new_content += self.parse_content(input['samples']) + '__tt__'
               if (input == content[-1]):
                   new_content += self.parse_content(input['input'])
               else:
                   new_content += self.parse_content(input['input']) + ':'
        except Exception, ex:
            self.stop_err('Error (line:%s)chip_parse_input\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))
        return new_content

    # error
    def stop_err(self, msg ):
        sys.stderr.write( "%s\n" % msg )
        sys.exit(2)
        
    # email
    def send_email(self, username, run_id, config_type, html):
        if (config_type != 'Docker' and config_type != 'Travis'):
            email_sender=self.config.get(self.params_section, "email_sender")
            email_err_receiver=self.config.get(self.params_section, "email_err_receiver")
            run_sql = "SELECT run_status FROM ngs_runparams where id = %s;"%run_id
            end_email_check=self.runSQL(run_sql%locals())
            user_sql = "SELECT name, email, email_toggle FROM users where username = '%s';"%username
            email_check=self.runSQL(user_sql%locals())
            if (end_email_check[0][0] == 1 and email_check[0][2] == 1):
                receiver = email_check[0][1]
                subject = 'Your Dolphin run has completed!'
                body = 'Your Dolphin run #%s has completed successfully!' % run_id
            elif (end_email_check[0][0] != 1):
                receiver =  email_err_receiver
                subject = 'There has been an error in run: %s' % run_id
                body = "Run %s has ended with an error in: %s.  Error type: %s" % (run_id, config_type, end_email_check[0][0])
                body +="\nYou may visit the status page by clicking this link:\n %s/stat/reroute/%s" % (html, run_id)
            p = os.popen("%s -t" % "/usr/sbin/sendmail", "w")
            p.write("From: %s\n" % email_sender)
            p.write("To: %s\n" % receiver)
            p.write("Subject: %s\n" % subject)
            p.write("\n") # blank line separating headers from body
            p.write("%s" % body)
            status = p.close()

# main
def main():
    
   params_section = ""
   
   try:
        tmpdir = '../tmp/files'
        logdir = '../tmp/logs'
        
        if not os.path.exists(tmpdir):
           os.makedirs(tmpdir)
        if not os.path.exists(logdir):
           os.makedirs(logdir)
         #define options
        parser = OptionParser()
        parser.add_option("-r", "--rungroupid", dest="rpid")
        parser.add_option("-b", "--backup", dest="backup")
        parser.add_option("-w", "--wkey", dest="wkey")
        parser.add_option("-c", "--config", dest="config")
        # parse
        options, args = parser.parse_args()
        # retrieve options
        rpid    = options.rpid
        BACKUP  = options.backup
        WKEY    = options.wkey
        params_section = options.config        
        
        dolphin=Dolphin(params_section)
        logging.basicConfig(filename=logdir+'/run'+str(rpid)+'/run.'+str(rpid)+'.'+str(os.getpid())+'.log', filemode='w',format='%(asctime)s %(message)s', datefmt='%m/%d/%Y %I:%M:%S %p', level=logging.DEBUG)

        if (not rpid):
           rpid=-1
        runidstr=" -r "+str(rpid)
        
        dolphin.config.read("../config/config.ini")
        print dolphin.params_section
        logging.info(dolphin.params_section)
        runparamsids=dolphin.getRunParamsID(rpid)
        for runparams_arr in runparamsids:
           runparamsid=runparams_arr[0]
           username=runparams_arr[1]
           isbarcode=runparams_arr[2]
           print runparams_arr
           logging.info(runparams_arr)

           amazon = dolphin.getAmazonCredentials(username)
           backupS3="Yes"
           amazonupload="No"
           if (amazon != () and BACKUP):
              amazonupload     = "Yes"

           (inputdir, backup_dir, amazon_bucket, outdir) = dolphin.getDirs(runparamsid, isbarcode)
           if(inputdir == backup_dir and outdir.find("/initial_run")==-1 ):
              backupS3 = None
              gettotalreads = None
           else:
              gettotalreads = "Yes"

           print "%s %s %s %s"%(inputdir, backup_dir, amazon_bucket, outdir)
           logging.info("%s %s %s %s"%(inputdir, backup_dir, amazon_bucket, outdir))
           
           if (isbarcode):
               spaired, inputparams, barcodes=dolphin.getLaneList(runparamsid)
           else:
               spaired, inputparams=dolphin.getSampleList(runparamsid)
               barcodes    = None

           runparams = dolphin.getRunParams(runparamsid)
           logging.info(runparams)
    
           input_fn      = logdir+'/run'+str(rpid)+'/input.'+str(rpid)+'.'+str(os.getpid())+'.txt'
           
           if not outdir :
              print >>stderr, 'Error: Output dir is NULL.'
              exit( 128 )

           content = dolphin.parse_content( inputparams )
          
           dolphin.writeInput(input_fn, inputdir, content, runparams, barcodes)

           workflow = logdir+'/run'+str(rpid)+'/seqmapping_workflow.'+str(rpid)+'.'+str(os.getpid())+'.txt'

           dolphin.writeWorkflow(workflow, gettotalreads, amazonupload, backupS3, runparamsid, runparams)
           dolphin_tools_dir=dolphin.config.get(dolphin.params_section, "dolphin_tools_src_path") 
           dolphin_default_params=dolphin.config.get(dolphin.params_section, "dolphin_default_params_path")
           wkeystr=''
           if (WKEY):
               wkeystr=' -k '+str(WKEY)
           logging.info("CMD:%s"%(dolphin.cmd % locals()))
           print dolphin.cmd % locals()
           print "\n\n\n"
           p = subprocess.Popen(dolphin.cmd % locals(), shell=True, stdout=subprocess.PIPE)
           dolphin.updatePID(runparamsid, p.pid)

           for line in p.stdout:
              print(str(line.rstrip()))
              logging.info(str(line.rstrip()))
              p.stdout.flush()
              if (re.search('failed\n', line) or re.search('Err\n', line) ):
                 logging.info("failed")
                 dolphin.send_email(runparamsids[0][1], runparamsids[0][0], dolphin.params_section, dolphin.config.get(dolphin.params_section, "base_path"));
                 dolphin.stop_err("failed")
        #Send email when finished
        dolphin.send_email(runparamsids[0][1], runparamsids[0][0], dolphin.params_section, dolphin.config.get(dolphin.params_section, "base_path") );
   except Exception, ex:
        dolphin.stop_err('Error (line:%s)running dolphin_wrapper.py\n%s'%(format(sys.exc_info()[-1].tb_lineno), str(ex)))


if __name__ == "__main__":
    main()
