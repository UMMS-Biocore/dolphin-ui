import os, re, string, sys, commands
import logging
import urllib,urllib2
import warnings
import json
import boto3
import MySQLdb
import ConfigParser
from boto3.s3.transfer import S3Transfer
from botocore.client import Config
from sys import argv, exit, stderr
from optparse import OptionParser
from config import *
from workflowdefs import *
sys.path.insert(0, sys.argv[4])
from funcs import *

class botoSubmit:
    f=""
    config = ConfigParser.ConfigParser()
    params_section = ''
    
    def __init__(self, f, params_section):
        self.f = f
        self.params_section = params_section
        
    def setURL(self, url):
        self.url = url
        
    def runSQL(self, sql):
        try:
            print self.params_section
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
            db.commit()
            db.close()
            return results
        except Exception, ex:
            print ex
        
    def getAmazonCredentials(self, username):
        data = urllib.urlencode({'func':'getAmazonCredentials', 'username':username})
        ret = self.f.queryAPI(self.url, data, "getAmazonCredentials:"+str(username))
        if (len(ret)>2):
            ret=json.loads(ret)[0]
        else:
            ret=''
        return ret
    
    def getSampleList(self, runparamsid, barcode):  
        data = urllib.urlencode({'func':'getSelectedSampleList', 'sampleids':str(runparamsid), 'barcode':str(barcode)})
        ret = self.f.queryAPI(self.url, data, "getSampleList:"+runparamsid)
        if (ret):
           ret=json.loads(ret)
        return ret
            
    def getValfromFile(self,filename):
        val=''
        if (os.path.isfile(filename)):
            infile = open(filename)
            val = infile.readlines()[0].rstrip()
        else:
            print "Filename:"+filename
            sys.exit(84)
        return val
    
    def processFastqFiles(self, sample, paired):
        try:
            md5sum=''
            count=''
            libname=sample['samplename']
            backup_dir=sample['backup_dir']
            filename=''
      
            if (paired):
                firstCount=self.getValfromFile(backup_dir+'/'+libname+'.1.fastq.gz.count')
                secondCount=self.getValfromFile(backup_dir+'/'+libname+'.2.fastq.gz.count')
                if (firstCount == secondCount):
                    count=firstCount
                    filename= libname+'.1.fastq.gz,'+libname+'.2.fastq.gz'
                    firstmd5sum=self.getValfromFile(backup_dir+'/'+libname+'.1.fastq.gz.md5sum').split(' ')[0]
                    secondmd5sum=self.getValfromFile(backup_dir+'/'+libname+'.2.fastq.gz.md5sum').split(' ')[0]
                    md5sum=firstmd5sum+','+secondmd5sum
                else:
                    print "ERROR 85: The # of reads in paired end libary not equal"
                    print "%s"%(backup_dir+'/'+libname+'.1.fastq.gz.count')
                    #sys.exit(85)
            else:
                filename= libname+'.fastq.gz'
                count=self.getValfromFile(backup_dir+'/'+libname+'.fastq.gz.count')
                md5sum=self.getValfromFile(backup_dir+'/'+libname+'.fastq.gz.md5sum')
            sample_id=self.getFastqFileId(sample)
            
            if (sample_id>0):
                self.updateFastqFile( md5sum, count, filename, sample)
            else:
                self.insertFastqFile(md5sum, count, filename, sample)
    
        except Exception, ex:
            print ex
    
    def getFastqFileId(self, sample):
        data = urllib.urlencode({'func':'getFastqFileId', 'sample_id':str(sample['sample_id'])})
        ret = self.f.queryAPI(self.url, data, "getFastqFileId:"+str(sample['sample_id']))
        ret = json.loads(ret)
        if (len(ret) > 0):
            ret = ret[0]['sample_id']
        else:
            ret = 0
        return ret
    
    def updateFastqFile(self, md5sum, count, filename, sample):
        sample_id=sample['sample_id']
        owner_id=sample['owner_id']
        if (count!=""):
           data = urllib.urlencode({'func':'upadateFastqFile', 'sample_id':str(sample_id), 'owner_id':str(owner_id), 'md5sum':str(md5sum), 'total_reads':str(count)})
           ret = eval(self.f.queryAPI(self.url, data, "upadateFastqFile:"+str(sample_id)))
    
    def insertFastqFile(self, checksum, total_reads, filename, sample):
        if (total_reads != ""):
            data = urllib.urlencode({'func':'insertFastqFile', 'filename':str(filename),'total_reads':str(total_reads),'checksum':str(checksum), 'sample_id':str(sample['sample_id']),'lane_id':str(sample['lane_id']),'dir_id': str(sample['dir_id']),'owner_id':str(sample['owner_id']), 'group_id':str(sample['group_id']), 'perms':str(sample['perms'])})
            ret = eval(self.f.queryAPI(self.url, data, "insertFastqFile:"+str(sample['sample_id'])))
    
    def checkReadCounts(self, sample_id, tablename):
        data = urllib.urlencode({'func':'checkReadCounts', 'sample_id':str(sample_id), 'tablename':str(tablename)})
        ret = self.f.queryAPI(self.url, data, "checkReadCounts:"+str(sample_id))
        ret = json.loads(ret)
        return ret['Result']
    
    def uploadFile(self, amazon, amazon_bucket, fastq_dir, filename):
        try:
            s3 = boto3.resource('s3', 'us-east-1',
            aws_access_key_id=amazon['aws_access_key_id'],
            aws_secret_access_key=amazon['aws_secret_access_key'])
            
            p = amazon_bucket.split("/")
            s3_file_name = "%s/%s"%(str(re.sub(p[0]+'/', '', amazon_bucket)), filename)
            inputfile = "%s/%s"%(fastq_dir.strip(), filename)
            amazon_bucket = p[0]
            
            print 'Upload started[%s]=>[%s]'%(inputfile, s3_file_name)
            s3_bucket = s3.Bucket(str(amazon_bucket))
            s3_object = s3_bucket.Object(s3_file_name)
            mpu = s3_object.initiate_multipart_upload()
            
            # chunk , min size 5mb
            CHUNK = 5 * 1024 * 1024
            
            part_info = {'Parts': []}
            
            with open(inputfile, 'rb') as f:
                while True:
                    # read chunk from file
                    chunk = f.read(CHUNK);
                    if not chunk:
                        # complete
                        mpu.complete(MultipartUpload=part_info)
                        break
                    # part number
                    part_nr = len(part_info['Parts'])+1
                    # create part
                    part = mpu.Part(part_nr)
                    # upload
                    response = part.upload(Body=chunk)
                    # add part
                    part_info['Parts'].append({
                        'PartNumber': part_nr,
                        'ETag': response['ETag'] 
                    })

        except Exception, ex:
            print ex
            
def main():
    f = funcs()
    params_section = ""
    
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
    
    boto = botoSubmit(f, params_section)
    boto.config.read("../config/config.ini")
    url = boto.config.get(params_section, "api_path")+'/api/service.php'
    boto.setURL(url)
    
    amazon = boto.getAmazonCredentials('kucukura')
    samplelist=boto.getSampleList(sys.argv[3], 'none')
    
    processedLibs=[]
    amazon_bucket=""
    for sample in samplelist:
        print "\n"
        sample_id=sample['sample_id']
        file_id=sample['file_id']
        libname=sample['samplename']  
        filename=sample['file_name']
        backup_dir=sample['backup_dir']
        amazon_bucket=sample['amazon_bucket']
        
        PAIRED=None
        if (filename.find(',')!=-1):
           PAIRED="Yes"
    
        boto.processFastqFiles(sample, PAIRED)
        processedLibs.append([libname, sample_id])

        amazon_bucket = str(re.sub('s3://', '', amazon_bucket))
        print libname + ":" + str(sample_id)
        if (boto.checkReadCounts(sample_id, 'ngs_fastq_files') and amazon):
            if (filename.find(',')!=-1):
                files=filename.split(',')
                boto.uploadFile(amazon, amazon_bucket, backup_dir, libname+'.1.fastq.gz' )
                boto.uploadFile(amazon, amazon_bucket, backup_dir, libname+'.2.fastq.gz' )
            else:
                boto.uploadFile(amazon, amazon_bucket, backup_dir, libname+'.fastq.gz' )
            boto.runSQL("UPDATE ngs_fastq_files SET backup_checksum = NULL, aws_status = 0 WHERE sample_id in ("+sample_id+")")
        else:
            print "ERROR 86: The # of read counts doesn't match: %s",libname
            sys.exit(86)
    
    sys.exit(0)

if __name__ == "__main__":
    main()
    