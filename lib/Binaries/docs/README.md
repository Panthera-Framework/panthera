Binaries
========

Binaries are executable, independent scripts that implements "Application" interface.
"Application" interface gives access to database, translations, and which is most important - to easy shell arguments parsing and executing args and opts.
The only one thing that is missing here is threading, but it's missing because PHP does not support it.