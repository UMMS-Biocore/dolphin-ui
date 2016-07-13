#!/usr/bin/env python
import os, re, string, sys
import warnings
import json
import time
import urllib,urllib2
from sys import argv, exit, stderr
sys.path.insert(0, sys.argv[2])
from config import *
from funcs import *

class cronMD5Sum:
    url=""
    f=""
    def __init__(self, url, f ):
        self.url = url
        self.f = f
    def getAllFastqInfo(self):  
        data = urllib.urlencode({'func':'getAllFastqInfo'})
        ret = self.f.queryAPI(self.url, data)
        if (ret):
            ret=json.loads(ret)
        return ret
    def runMD5SumUpdate(self, clusteruser, backup_dir, file_name):
        data = urllib.urlencode({'func':'runMD5SumUpdate', 'clusteruser':str(clusteruser), 'backup_dir':str(backup_dir), 'file_name':str(file_name)})
        ret = self.f.queryAPI(self.url, data)
    
def main():
    try:
        CONFIG = sys.argv[1]
        TOOLS_DIR = sys.argv[2]
        print CONFIG
        print TOOLS_DIR
    except:
        print "cronMD5Sum.py takes in two arguments <Config type> <Dolphin tools/src directory>"
        sys.exit(2)
    
    f = funcs()
    config = getConfig(CONFIG)
    md5sum = cronMD5Sum(config['url'], f)
    
    filelist = md5sum.getAllFastqInfo()
    print "\n"
    for f in filelist:
        clusteruser=f['clusteruser']
        backup_dir=f['backup_dir']
        file_name=f['file_name']
        print file_name
        md5sum.runMD5SumUpdate(clusteruser, backup_dir, file_name)
        print "\n"
    
main()