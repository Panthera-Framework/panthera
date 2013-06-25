#!/usr/bin/env python
""" Gettext to php serialized array converter """

import os
import sys
import re
import json
from subprocess import Popen, PIPE, STDOUT
import base64
import phpserialize

class gettextTopantheraLocale:
    """ Gettext to JSON converter """

    memory = dict()
    path = ""
    
    def __init__(self, path):
        """ Constructor 
        
        Keyword arguments:
        path -- absolute path to gettext .po file
        
        """
    
        self.path = path
    
    def main(self):
        """ Main function, do all stuff """
    
        # read file
        f = open(self.path, "r")
        c = f.read()
        f.close()
        
        # this will be our storage
        memory = dict()
        lastMsgid = ""        

        lines = c.split("\n")

        for line in lines:
        
            if line[0:5].lower() == "msgid":
                lastMsgid = line[5:].strip()[1:-1]
                
            if line[0:6].lower() == "msgstr":
                memory[str(lastMsgid)] = str(line[6:].strip()[1:-1])
                
        
        return phpserialize.dumps(memory)

if len(sys.argv) < 2:
    print("You need to specify input file")
    sys.exit(1)
    
if (os.path.isdir(sys.argv[1])):
    d = os.listdir(sys.argv[1])

    print("Converting domains in "+sys.argv[1]+" directory:")    
    for f in d:
        info = os.path.splitext(f)
        
        if info[1] == ".po":
            print("+ "+info[0])
            app = gettextTopantheraLocale(sys.argv[1]+"/"+f)
            
            newFile = open(sys.argv[1]+"/"+info[0]+".phps", "w")
            newFile.write(app.main())
            newFile.close()

    print("\nHuh, done.")            
else:
    app = gettextTopantheraLocale(sys.argv[1])
    print app.main()





