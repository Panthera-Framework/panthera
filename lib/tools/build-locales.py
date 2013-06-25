#!/usr/bin/env python
import os
import sys
import glob
import time
import hashlib
#msgfmt -o "+mo+".mo "+k

def buildFile(poFile):
    print("msgfmt -o "+poFile.replace(".po", "")+".mo "+poFile)
    os.system("msgfmt -o "+poFile.replace(".po", "")+".mo "+poFile)
    posData[hashlib.md5(poFile).hexdigest()] = {'file': poFile, 'time': os.path.getmtime(poFile)}


def build(pos):
    global posData

    for poFile in pos:
        buildFile(poFile)



automatic = False

if len(sys.argv) > 1:
    if sys.argv[1] == "-a":
        automatic = True

pos = glob.glob(os.getcwd()+"/locales/*/*/*/*.po")

print("Building locales from "+os.getcwd()+"/locales/")

posData = dict()

build(pos)

if automatic == True:
    while True:

        for poFile in posData:
            if posData[poFile]['time'] <  os.path.getmtime(posData[poFile]['file']):
                buildFile(posData[poFile]['file'])

        time.sleep(0.5)

