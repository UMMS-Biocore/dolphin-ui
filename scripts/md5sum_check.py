#!/share/bin/python

from optparse import OptionParser
import os
import urllib, json
import re
import sys
import re
import cgi
import sys

url=sys.argv[1]+"/ajax/dolphinfuncs.php?p=getSelectedFileList&samples="+sys.argv[2]
updateurl=sys.argv[1]+"/ajax/dolphinfuncs.php?p=updateRecheckChecksum&input=%(input)s&dirname=%(dirname)s&hashstr=%(hashstr)s"
   
def getFileList():
    response = urllib.urlopen(url);
    data = json.loads(response.read())
    print data
    return data

def getValfromFile(filename):
    val=''
    if (os.path.isfile(filename)):
        infile = open(filename)
        val = infile.readlines()[0].rstrip()
    else:
        sys.exit(84)
    return val
 
def calcHash(inputfile):
   command ="md5sum "+inputfile+" > "+inputfile+".md5sum;"
   print command
   child = os.popen(command)
   jobout = child.read().rstrip()
   err = child.close()
   hashstr = getValfromFile(inputfile+".md5sum").split(" ")[0]
   child = os.popen(command)
   print hashstr
   return hashstr

def runCalcHash(input, dirname):
    files=input.split(',')
    if (len(files)>1):
        hashstr1=calcHash(dirname + "/" + files[0].strip())
        hashstr2=calcHash(dirname + "/" + files[1].strip())
        hashstr=hashstr1+","+hashstr2
    else:
        hashstr=calcHash(dirname + "/" + input.strip())
    print hashstr
    input=urllib.quote(input)
    dirname=urllib.quote(dirname)
    urlstr=updateurl%locals()
    print urlstr
    response = urllib.urlopen(urlstr)

def main():
    results=getFileList()
    for result in results:
        fastq_dir=result['backup_dir']
        filename=result['file_name']
        print fastq_dir
        print filename
        runCalcHash(filename, fastq_dir)

if __name__ == "__main__":
    main()

