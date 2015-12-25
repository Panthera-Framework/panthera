Binaries
========

Binaries are executable, independent scripts that implements "Application" interface.
"Application" interface gives access to database, translations, and which is most important - to easy shell arguments parsing and executing args and opts.
The only one thing that is missing here is threading, but it's missing because PHP does not support it.

All of those executables are easily accessible from PF2 shell:

```bash
./.content/Binaries/shell
```

***Note:** __if ./.content/Binaries/shell file does not exists you have to run `deploy Build/Environment/ShellConfiguration`__