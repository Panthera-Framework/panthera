panthera
========

Simple web framework written in PHP, designed to be simple as possible, modular and easy to use. Inspired by Wordpress.

Our Team
=======
- Damian Kęska - co-founder, main programmer, translator, website maintainer
- Mateusz Warzyński - co-founder, programmer, translator, website maintainer
- Ricky Reed - main graphics designer, author of project's logo
- Dawid Niedźwiedzki - tester

Notice
=======

Panthera is still in beta development, we already made an installer, composer integration and many things to make it easier to install.

Installation
======

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
