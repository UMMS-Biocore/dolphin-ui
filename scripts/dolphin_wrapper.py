"""
    This tool recursively map sequenced files to any number of index files in given order.
    usage: dophin_wrapper.py [options]
"""
# imports
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

warnings.filterwarnings('ignore', '.*the sets module is deprecated.*',
         DeprecationWarning, 'MySQLdb')

from workflowdefs import *

bin_dir = dirname(argv[0])
cmd = 'python %(dolphin_tools_dir)s/runWorkflow.py -i %(input_fn)s -d %(galaxyhost)s -w %(workflow)s -p %(dolphin_default_params)s/Dolphin_v1.3_default.txt -u %(username)s -o %(outdir)s'
Config = ConfigParser.ConfigParser()
params_section = 'Default'

def runSQL(sql):
    db = MySQLdb.connect(
      host = Config.get(params_section, "db_host"),
      user = Config.get(params_section, "db_user"),
      passwd = Config.get(params_section, "db_password"),
      db = Config.get(params_section, "db_name"),
      port = int(Config.get(params_section, "db_port")))
    try:
      cursor = db.cursor()
      cursor.execute(sql)
      #print sql
      results = cursor.fetchall()
      cursor.close()
      del cursor

    except Exception, err:
      print "ERROR DB:for help use --help"
      db.rollback()
      sys.stderr.write('ERROR: %s\n' % str(err))
      sys.exit(2)
    finally:
      db.commit()
      db.close()
    return results

def getRunParamsID(rpid):
    rpstr="";
    if (rpid > 0):
       rpstr=" AND nrp.id=%s"%str(rpid)
    sql = "SELECT DISTINCT nrl.run_id, u.username, nrp.barcode from biocore.ngs_runlist nrl, biocore.ngs_runparams nrp, biocore.users u where nrp.id=nrl.run_id and u.id=nrl.owner_id and nrp.run_status=0 %s;"%rpstr
    return runSQL(sql)

def getRunParams(runparamsid):
    sql = "SELECT json_parameters from biocore.ngs_runparams where run_status=0 and id='%d'"%runparamsid
    result = runSQL(sql)
    for row in result:
        #print row[0]
        return json.loads(row[0])

def getAmazonCredentials(username):
    sql = 'SELECT DISTINCT ac.* FROM biocore.amazon_credentials ac, biocore.group_amazon ga, biocore.users u where ac.id=ga.amazon_id and ga.group_id=u.group_id and u.username="'+username+'";'
    results = runSQL(sql)

    return results

def getDirs(runparamsid, isbarcode):

    tablename="ngs_fastq_files"
    fields="d.backup_dir fastq_dir, d.backup_dir, d.amazon_bucket, rp.outdir, s.organism, s.library_type"
    idmatch="s.id=tl.sample_id"
    sql = "SELECT DISTINCT %(fields)s FROM biocore.ngs_runlist nr, biocore.ngs_samples s, biocore.%(tablename)s tl, biocore.ngs_dirs d, biocore.ngs_runparams rp where nr.sample_id=s.id and rp.run_status=0 and %(idmatch)s and d.id=tl.dir_id and rp.id=nr.run_id and nr.run_id='"+str(runparamsid)+"';"

    results=runSQL(sql%locals())
    if (not results):
       fields="d.fastq_dir, d.backup_dir, d.amazon_bucket, rp.outdir, s.organism, s.library_type"
       if (isbarcode):
           idmatch="s.lane_id=tl.lane_id"
           tablename="ngs_temp_lane_files"
       else:
           tablename="ngs_temp_sample_files"
       results=runSQL(sql%locals())
    return results[0]

def getSampleList(runparamsid):
    tablename="ngs_fastq_files"
    sql = "SELECT s.name, s.adapter, s.genotype, s.name, ts.file_name FROM biocore.ngs_runparams nrp, biocore.ngs_runlist nr, biocore.ngs_samples s, biocore.%(tablename)s ts, biocore.ngs_dirs d where nr.run_id=nrp.id and nr.sample_id=s.id and nrp.run_status=0 and s.id=ts.sample_id and d.id=ts.dir_id and nr.run_id='"+str(runparamsid)+"';"
    samplelist=runSQL(sql%locals())
    if (not samplelist):
        tablename="ngs_temp_sample_files"
        samplelist=runSQL(sql%locals())
    return getInputParams(samplelist)

def getInputParams(samplelist):
    inputparams=""
    for row in samplelist:
        if (inputparams):
           inputparams=inputparams+":"
        inputparams=inputparams+row[3]+","+row[4]
    spaired=None
    if (',' in row[4]):
        spaired="paired"
    return (spaired, inputparams)

def getLaneList(runparamsid):
    tablename="ngs_fastq_files"
    sql = "SELECT DISTINCT %(fields)s FROM biocore.ngs_runlist nrl, biocore.ngs_runparams nrp, biocore.ngs_samples s, biocore.%(tablename)s tl where nrl.run_id=nrp.id and nrp.run_status=0 and s.lane_id = tl.lane_id and s.id=nrl.sample_id and nrp.id='"+str(runparamsid)+"';"
    fields='tl.file_name'

    result=runSQL(sql%locals())
    if (not result):
        tablename="ngs_temp_lane_files"
        result=runSQL(sql%locals())

    inputparams=""
    for row in result:
        if (inputparams):
            inputparams=inputparams+":"
        inputparams=inputparams+row[0]
    spaired=None
    if (',' in row[0]):
        spaired="paired"

    fields='s.name, s.barcode'
    result=runSQL(sql%locals())
    if (not result):
        tablename="ngs_fastq_files"
        result=runSQL(sql%locals())

    barcode="Distance,1:Format,5"
    for row in result:
        barcode=barcode+":"+row[0]+","+row[1]
    return (spaired, inputparams, barcode)


# error
def stop_err( msg ):
    sys.stderr.write( "%s\n" % msg )
    sys.exit()

# main
def main():
   try:
        tmpdir = '../tmp/files'
        if not os.path.exists(tmpdir):
           os.makedirs(tmpdir)
         #define options
        parser = OptionParser()
        parser.add_option("-r", "--rungroupid", dest="rpid")
        parser.add_option("-b", "--backup", dest="backup")
        # parse
        options, args = parser.parse_args()
        # retrieve options
        rpid    = options.rpid
        BACKUP    = options.backup
        if (not rpid):
           rpid=-1

        Config.read("../config/config.ini")
        params_section = "Default"
        if (os.environ.has_key('DOLPHIN_PARAMS_SECTION')):
            params_section=os.environ['DOLPHIN_PARAMS_SECTION']
        print params_section
        runparamsids=getRunParamsID(rpid)
        for runparams_arr in runparamsids:
           runparamsid=runparams_arr[0]
           username=runparams_arr[1]
           isbarcode=runparams_arr[2]
           print runparams_arr

           amazon = getAmazonCredentials(username)
           backupS3="Yes"
           amazonupload="No"
           if (amazon != () and BACKUP):
              amazonupload     = "Yes"

           (inputdir, backup_dir, amazon_bucket, outdir, organism, library_type) = getDirs(runparamsid, isbarcode)
           if(inputdir == backup_dir):
              backupS3 = None
              gettotalreads = None
           else:
              gettotalreads = "Yes"

           print "%s %s %s %s %s %s"%(inputdir, backup_dir, amazon_bucket, outdir, organism, library_type )

           if (isbarcode):
               spaired, inputparams, barcodes=getLaneList(runparamsid)
           else:
               spaired, inputparams=getSampleList(runparamsid)
               barcodes    = None

           runparams = getRunParams(runparamsid)

           input_fn      = "../tmp/files/input.txt"

           fastqc      = runparams.get('fastqc')
           adapter     = runparams.get('adapter')
           quality     = runparams.get('quality')
           trim        = runparams.get('trim')
           clean       = runparams.get('clean')
           trimpaired  = runparams.get('trimpaired')
           split       = runparams.get('split')
           commonind   = runparams.get('commonind')
           advparams   = runparams.get('advparams')
           resume      = runparams.get('resume')
           customind   = runparams.get('customind')
           pipeline    = runparams.get('pipeline')
           genomebuild = runparams.get('genomebuild')

           #print inputparams
           #print barcodes
           #print spaired
           #print "adapter:"+adapter
           #print "quality:"+quality
           #print "trim:"+trim
           #print "fastqc:"+fastqc

           if customind:
              customind    = [i for i in customind]

           if pipeline:
              pipeline     = [i for i in pipeline]
           #print pipeline

           if not outdir :
              print >>stderr, 'Error: Output dir is NULL.'
              exit( 128 )

           content = parse_content( inputparams )
           if commonind:
              commonind = re.sub('test', '', commonind)

           write_input(input_fn, inputdir, content, genomebuild, spaired, barcodes, adapter, quality, trim, trimpaired, split, commonind, advparams, customind, pipeline )

           workflow = join( bin_dir, 'seqmapping_workflow.txt' )

           write_workflow(resume, gettotalreads, amazonupload, backupS3, runparamsid, customind, commonind, pipeline, barcodes, fastqc, adapter, quality, trim, split, workflow, clean)

           galaxyhost=Config.get(params_section, "galaxyhost")
           dolphin_tools_dir=Config.get(params_section, "dolphin_tools_src_path") 
           dolphin_default_params=Config.get(params_section, "dolphin_default_params_path")

           print cmd % locals()
           print "\n\n\n"
           p = subprocess.Popen(cmd % locals(), shell=True, stdout=subprocess.PIPE)

           for line in p.stdout:
              print(str(line.rstrip()))
              p.stdout.flush()
              if (re.search('failed\n', line) or re.search('Err\n', line) ):
                stop_err("failed")
   except Exception, ex:
        stop_err('Error running dolphin_wrapper.py\n' + str(ex))

   sys.exit(0)

def write_input( input_fn, data_dir, content,genomebuild,spaired,barcodes,adapter, quality, trim,trimpaired, split, commonind, advparams, customind, pipeline) :
   if ('DIR' in content):
       content = content.replace( 'DIR', data_dir )
   else:
       if (str(barcodes) != "None"):
           content = data_dir+"/"+content
           content = content.replace( ':', ":"+data_dir+"/" )
       content = content.replace( ',', ","+data_dir+"/" )
   content = re.sub(r'/+','/',content)

   gb=genomebuild.split(',')
   previous="NONE"
   fp = open( input_fn, 'w' )
   print >>fp, '@GENOME=%s'%gb[0]
   gb[1] = re.sub('test', '', gb[1])
   genomeindex=gb[1]

   print >>fp, '@VERSION=%s'%gb[1]
   print >>fp, '@INPUT=%s'%content
   print >>fp, '@DATADIR=%s'%data_dir
   print >>fp, '@OUTFILE=%s'%input_fn
   print >>fp, '@GENOMEBUILD=%s,%s'%(gb[0],gb[1])
   print >>fp, '@SPAIRED=%s'%spaired
   barcodeflag=0

   if (barcodes and barcodes.lower() != 'none'):
     print >>fp, '@BARCODES=%s'%parse_content(barcodes)
     barcodeflag=1
     previous="BARCODE"
   else:
     print >>fp, '@BARCODES=NONE'

   if (adapter and adapter.lower() != 'none'):
     print >>fp, '@ADAPTER=%s'%parse_content(adapter)
     print >>fp, '@PREVIOUSADAPTER=%s'%previous
     previous="ADAPTER"
   else:
     print >>fp, '@ADAPTER=NONE'

   if (quality and quality.lower() != 'none'):
     print >>fp, '@QUALITY=%s'%parse_content(quality)
     print >>fp, '@PREVIOUSQUALITY=%s'%previous
     previous="QUALITY"
   else:
     print >>fp, '@QUALITY=NONE'

   if (trim and trim.lower() != 'none'):
     print >>fp, '@TRIM=%s'%trim
     print >>fp, '@TRIMPAIRED=%s'%trimpaired
     print >>fp, '@PREVIOUSTRIM=%s'%previous
     previous="TRIM"
   else:
     print >>fp, '@TRIM=NONE'

   if(split and trim.lower() != 'none'):
     print >>fp, '@PREVIOUSSPLIT=%s'%previous
     previous="SPLIT"
   else:
     print >>fp, '@PREVIOUSSPLIT=NONE'


   if (commonind and commonind.lower() != 'none'):
     arr=re.split(r'[,:]+', parse_content(commonind))

     for i in arr:
       if(len(i)>1):
          default_bowtie_params="@DEFBOWTIE2PARAM"
          default_description="@DEFDESCRIPTION"
       print >>fp, '@PARAM%s=@GCOMMONDB/%s/%s,%s,%s,%s,1,%s'%(i,i,i,i,default_bowtie_params,default_description,previous)
       if (i != "ucsc" and i != gb[1]):
          previous=i

     if (advparams):
        print >>fp, '@ADVPARAMS=%s'%(parse_content(advparams))
     else:
        print >>fp, '@ADVPARAMS=NONE'

   mapnames=commonind
   if (not mapnames or mapnames.lower() == 'none'):
      mapnames="";
   if (customind):
      for i in customind:
        arr=re.split(r'[,:]+', parse_content(customind))
        index=parse_content(arr[0])
        name=parse_content(replace_space(arr[1]))
        mapnames=str(mapnames)+name+":"+index+","
        bowtie_params=parse_content(replace_space(arr[2]))
        description=parse_content(replace_space(arr[3]))
        filter_out=arr[4]

        print >>fp, '@PARAM%s=%s,%s,%s,%s,%s,%s'%(name,index,name,bowtie_params,description,filter_out,previous)
        if (str(filter_out)=="1"):
           previous=name

   if (pipeline):
       deseq_count=1
       for i in pipeline:
         arr=i.split(':')
         pipename=arr[0]
         if (pipename=="RNASeqRSEM"):
           paramsrsem=arr[1];
           if (not paramsrsem):
              paramsrsem="NONE"
           print >>fp, '@PARAMSRSEM=%s'%(parse_content(paramsrsem))
           print >>fp, '@TSIZE=50';
           print >>fp, '@PREVIOUSPIPE=%s'%(previous)

         if (pipename=="Tophat"):
           paramstophat=arr[1];
           if (not paramstophat):
              paramstophat="NONE"
           print >>fp, '@PARAMSTOPHAT=%s'%(parse_content(paramstophat))
         if (pipename=="DESeq"):
           print >>fp, '@COLS%s=%s'%(str(deseq_count), remove_space(arr[1]));
           print >>fp, '@CONDS%s=%s'%(str(deseq_count), remove_space(arr[2]));
           print >>fp, '@FITTYPE%s=%s'%(str(deseq_count), arr[3]);
           print >>fp, '@HEATMAP%s=%s'%(str(deseq_count), arr[4]);
           print >>fp, '@PADJ%s=%s'%(str(deseq_count), arr[5]);
           print >>fp, '@FOLDCHANGE%s=%s'%(str(deseq_count), arr[6]);
           deseq_count+=1

         if (pipename=="ChipSeq"):
           chipinput=parse_content(str(arr[1]))
           bowtie_params=remove_space("-k_%s"%(str(arr[2])))
           description="Chip_Mapping"
           filter_out="0"
           print >>fp, '@ADVPARAMS=NONE'
           print >>fp, '@CHIPINPUT=%s'%(chipinput)
           print >>fp, '@PARAMChip=@GCOMMONDB/%s/%s,Chip,%s,%s,%s,%s'%(gb[1],gb[1],bowtie_params,description,filter_out,previous)

           print >>fp, '@GENOMEINDEX=%s'%(genomeindex);
           print >>fp, '@TSIZE=%s'%(remove_space(str(arr[3])));
           print >>fp, '@BWIDTH=%s'%(remove_space(str(arr[4])));
           print >>fp, '@GSIZE=%s'%(remove_space(str(arr[5])));


       print >>fp, '@MAPNAMES=%s'%(mapnames)
       print >>fp, '@PREVIOUSPIPE=%s'%(previous)

       fp.close()

def write_workflow( resume, gettotalreads, amazonupload, backupS3, runparamsid, customind, commonind, pipeline, barcodes, fastqc, adapter, quality, trim, split, file, clean ):
   fp = open ( file, 'w')
   sep='\t'

   stepline=stepCheck % locals()
   print >>fp, '%s'%stepline
   if (barcodes and barcodes.lower() != 'none'):
       stepline=stepBarcode % locals()
       print >>fp, '%s'%stepline

   if (gettotalreads and gettotalreads.lower() != 'none'):
       stepline=stepGetTotalReads % locals()
       print >> fp, '%s'%stepline

   if (backupS3 and backupS3.lower() != 'none'):
       stepline=stepBackupS3 % locals()
       print >> fp, '%s'%stepline

   if (fastqc and fastqc.lower() == 'yes'):
      stepline=stepFastQC % locals()
      print >>fp, '%s'%stepline
      stepline=stepMergeFastQC % locals()
      print >>fp, '%s'%stepline

   if (adapter and adapter.lower() != 'none'):
      stepline=stepAdapter % locals()
      print >>fp, '%s'%stepline

   if (quality and quality.lower() != 'none'):
      stepline=stepQuality % locals()
      print >>fp, '%s'%stepline

   if (trim and trim.lower() != 'none'):
      stepline=stepTrim % locals()
      print >>fp, '%s'%stepline

   countstep = False
   if (commonind and commonind.lower() != 'none'):

      arr=re.split(r'[,:]+', parse_content(commonind))

      for i in arr:
         countstep = True
         if(len(i)>1):
           indexname=i
         stepline=stepSeqMapping % locals()
         print >>fp, '%s'%stepline


   if (customind):
      for i in customind:
         countstep = True
         arr=i.split(',')
         indexname=arr[1]
         stepline=stepSeqMapping % locals()
         print >>fp, '%s'%stepline

   if (split and split.lower() != 'none'):
      thenumberofreads=str(split)
      stepline=stepSplit % locals()
      print >>fp, '%s'%stepline

   if (pipeline):
      deseq_count=1
      for i in pipeline:
         arr=i.split(':')
         pipename=arr[0]
         if (pipename == "RNASeqRSEM"):
            stepline=stepRSEM % locals()
            print >>fp, '%s'%stepline
            g_i = "genes"
            t_e = "tpm"
            stepline=stepRSEMCount % locals()
            print >>fp, '%s'%stepline
            g_i = "genes"
            t_e = "expected_count"
            stepline=stepRSEMCount % locals()
            print >>fp, '%s'%stepline
            g_i = "isoforms"
            t_e = "tpm"
            stepline=stepRSEMCount % locals()
            print >>fp, '%s'%stepline
            g_i = "isoforms"
            t_e = "expected_count"
            stepline=stepRSEMCount % locals()
            print >>fp, '%s'%stepline
            igv=arr[2]
            if (igv.lower()=="yes"):
               type="RSEM"
               stepline=stepIGVTDF % locals()
               print >>fp, '%s'%stepline
            bam2bw=arr[3]
            if (bam2bw.lower()=="yes"):
               type="RSEM"
               stepline=stepBam2BW % locals()
               print >>fp, '%s'%stepline

         if (pipename == "Tophat"):
            stepline=stepTophat % locals()
            print >>fp, '%s'%stepline
            igv=arr[2]
            if (igv.lower()=="yes"):
               type="Tophat"
               stepline=stepIGVTDF % locals()
               print >>fp, '%s'%stepline
            bam2bw=arr[3]
            if (bam2bw.lower()=="yes"):
               type="Tophat"
               stepline=stepBam2BW % locals()
               print >>fp, '%s'%stepline

         if (pipename == "DESeq"):
            stepline=stepDESeq2 % locals()
            print >>fp, '%s'%stepline
            deseq_count += 1

         if (pipename == "ChipSeq"):
            #Arrange ChipSeq mapping step
            indexname='Chip'
            stepline=stepSeqMapping % locals()
            print >>fp, '%s'%stepline
            if (split):
                stepline=stepMergeChip % locals()
                print >>fp, '%s'%stepline
            #Set running macs step
            stepline=stepMACS % locals()
            print >>fp, '%s'%stepline
            stepline=stepAggregation % locals()
            print >>fp, '%s'%stepline

            igv=str(arr[6])
            if (igv.lower()=="yes"):
                type="chip"
                if (split):
                    type="mergechip"
                stepline=stepIGVTDF % locals()
                print >>fp, '%s'%stepline

            bam2bw=str(arr[7])
            if (bam2bw.lower()=="yes"):
                type="chip"
                if (split):
                    type="mergechip"
                stepline=stepBam2BW % locals()
                print >>fp, '%s'%stepline

   if (countstep):
      stepline=stepCounts % locals()
      print >>fp, '%s'%stepline
      stepline=stepMakeReport % locals()
      print >>fp, '%s'%stepline
   
   level=0
   if (clean):
      level=1
   stepline=stepClean % locals()
   print >>fp, '%s'%stepline

   fp.close()

class NoContentParsedError( Exception ) :

   pass
class ContentFormatError( Exception ) :
   pass

def replace_space(content) :
   content = re.sub('[\s\t,]+', '_', content)
   return content

def remove_space(content) :
   content = content.replace( '__cr____cn__', '' )
   content = re.sub('[\s\t\n]+', '', content)
   return content

def parse_content( content, ncols=8, base64=False, verbose=0 ) :
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
   content = re.sub('[\n:]+', ':', content)
   content = re.sub(':+', ':', content)
   content = re.sub(':$', '', content)
   #content = re.sub('[-]+', '_', content)
   return content

if __name__ == "__main__":
    main()
