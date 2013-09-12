Panthera Framework
========

Our project bet on simplicity, we don't want to have complicated interfaces, data models - we are creating everything in KISS rule. Of course, we have data models but they are more 
easier to understand than data models in other frameworks. 

Panthera is built on a monolithic kernel, all core elements ale built-in, we don't store every class in a single file.
Performance and and fexibility is very important - there are serval mechanisms making Panthera based application ready to use in big environments.

## Our Team
- Damian Kęska - co-founder, main programmer, translator, website maintainer
- Mateusz Warzyński - co-founder, programmer, translator, website maintainer
- The-Error - main graphics designer, giving us helpful tips about project design
- Dawid Niedźwiedzki - tester

## Notice
Panthera is still in beta development, we already made an installer, composer integration and many things to make it easier to install.

## Installation
Installation of Panthera Framework is very simple, but at this moment requires shell access to the server. In near future we plan making zipped packages with all dependencies to allow just place Panthera on shared hosting using FTP.

So, lets download fresh contents.

```bash
git clone https://github.com/webnull/panthera
cd panthera
./install.sh
```
And if you are not using account WWW server is using, you should allow your Nginx, Lighttpd or Apache to write to example-site directory.
It requires to create some directories and files, so make it writable.

```bash
chown www-data example-site -R
```
