#!/usr/bin/env python
#-*- encoding: utf-8 -*-

import sys
import os
import random
import string
import subprocess
import time
import json
import urlparse
from PyQt4 import Qt
import subprocess

__author__ = "Damian Kęska"
__license__ = "LGPLv3"
__maintainer__ = "Damian Kęska"
__copyright__ = "Copyleft by Panthera Team"

# get current working directory to include local files (debugging mode)
t = sys.argv[0].replace(os.path.basename(sys.argv[0]), "") + "src/"

if os.path.isdir(t):
    sys.path.append(t)
    
import pantheradesktop.kernel as baseKernel

class crontabApp(baseKernel.pantheraDesktopApplication):
    appName = "pantheraCrontab"
    threads = {}
    appDir = '.'
    qapp = None
    
    def executeJob(self, params, thread):
        command = 'echo \"'+params+'\" | php '+self.appDir+'/_crontab.php'
        
        self.logging.output('Executing "'+command+'"')
        task = subprocess.Popen([command], stdout=subprocess.PIPE, shell=True)
        data = task.communicate()[0]
        
        self.logging.output(data)
        self.logging.output('Task returned code '+str(task.returncode))
        thread.terminate()


    def updateTasksList(self):
        while True:
            data = json.loads(subprocess.check_output("echo \"?api=json&action=list\" | php _crontab.php", shell=True))
            
            if not data:
                self.logging.output('No input links found, bad response from server')
            
            if data['links']:
                for job in data['links']:
                    params = '?'+urlparse.urlparse(data['links'][job]).query
                    self.logging.output('Executing job: '+params)
                    
                    args = urlparse.parse_qs(params)
                    self.threads[args['jobname'][0]] = {}
                    self.threads[args['jobname'][0]]['appThread'], self.threads[args['jobname'][0]]['appWorker'] = baseKernel.createThread(self.executeJob, params, autostart=True)
                    time.sleep(0.2)
            
            time.sleep(60)
    
    def initApp(self, a=''):
        os.chdir(self.appDir)
        
        if not os.path.isfile(self.appDir+'/content/app.php'):
            self.logging.output(self.appDir+'/content/app.php not found, exiting')
            sys.exit(1)
            
        self.qapp = Qt.QApplication(sys.argv)
            
        self.updateTasksList()
    
    

kernel = crontabApp()
kernel.coreClasses['db'] = None # disable database
kernel.coreClasses['gui'] = None # disable gui
#kernel.coreClasses['argsparsing'] = buildZipArgs
kernel.initialize(quiet=False)
kernel.main(kernel.initApp)