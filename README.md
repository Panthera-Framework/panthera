Panthera Framework 2
====================

[![Code Climate](https://codeclimate.com/github/Panthera-Framework/panthera/badges/gpa.svg)](https://codeclimate.com/github/Panthera-Framework/panthera)
[![Test Coverage](https://codeclimate.com/github/Panthera-Framework/panthera/badges/coverage.svg)](https://codeclimate.com/github/Panthera-Framework/panthera/coverage)
[![Testing on Linux](https://travis-ci.org/Panthera-Framework/panthera.svg)](https://travis-ci.org/Panthera-Framework/panthera)
[![Testing on Windows](https://ci.appveyor.com/api/projects/status/teku9sij735ivmhn?svg=true)](https://ci.appveyor.com/project/webnull/panthera)

![screenshot](http://oi59.tinypic.com/2mypxr5.jpg)

Project assumptions:

- Overlay-like file structure, so every framework file could be replaced easily
- Deployment system
- Packages based, every feature is packaged, but is not a plugin (does not slow down application)
- Built-in shell with commands for managing your project
- Simple ORM
- Support for Doctrine DBAL to attach as an additional `Component` (@todo)
- Divided into replaceable Components


## How to setup a developer environment
Please see [`example-panthera-application`](https://github.com/Panthera-Framework/example-panthera-application) project which was created to be a project skeleton
for Panthera Framework 2 based applications.
