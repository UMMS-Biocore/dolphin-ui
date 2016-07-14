#!/usr/bin/env python
import os, re, string, sys
import warnings
import ConfigParser
import json
import time
import urllib,urllib2
from sys import argv, exit, stderr

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
        params_section = sys.argv[1]
        print params_section
    except:
        print "cronMD5Sum.py takes in 1 argument <params section>"
        sys.exit(2)
    configparse = ConfigParser.ConfigParser()
    configparse.read("../config/config.ini")
    sys.path.insert(0, configparse.get(params_section, 'DOLPHIN_TOOLS_SRC_PATH'))
    
    f = __import__('funcs').funcs()
    config = __import__('config').getConfig(params_section)
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