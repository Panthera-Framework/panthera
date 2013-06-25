#!/usr/bin/env python
import os
import time
import sys

#PANTHERA_DIR = os.path.dirname(os.path.realpath(__file__)).replace("/lib/tools", "")

file = "content/tmp/debug.log"

if not os.path.isfile(file):
    print("Cannot open file "+file)
    sys.exit(1)

lastMod = os.path.getmtime(file)

print("Listening on file "+file)

while True:
    time.sleep(0.2)

    if os.path.getmtime(file) > lastMod:
        os.system("clear")
        
        try:
            c = open(file, "r")
            content = c.read()

            if len(sys.argv) > 1:
                if "Client addr("+sys.argv[1]+") => " in content:
                    print(content)
            else:
                print(content)

            c.close()
            lastMod = os.path.getmtime(file)
        except Exceptiona as e:
            pass
