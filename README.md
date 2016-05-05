# This project is not maintained anymore, and there are no plans to maintain it
# Dependent project "RaspAP" will be rewrited into Symfony2

Panthera Framework 2
====================

[![Code Climate](https://codeclimate.com/github/Panthera-Framework/panthera/badges/gpa.svg)](https://codeclimate.com/github/Panthera-Framework/panthera)
[![Test Coverage](https://codeclimate.com/github/Panthera-Framework/panthera/badges/coverage.svg)](https://codeclimate.com/github/Panthera-Framework/panthera/coverage)
[![Testing on Linux](https://travis-ci.org/Panthera-Framework/panthera.svg)](https://travis-ci.org/Panthera-Framework/panthera)
[![Testing on Windows](https://ci.appveyor.com/api/projects/status/teku9sij735ivmhn?svg=true)](https://ci.appveyor.com/project/webnull/panthera)

![screenshot](http://oi59.tinypic.com/2mypxr5.jpg)

## Why Panthera Framework 2?

Because of it's simplicty, and Unix ideas implemented in elegant way.

- "Microkernel structure", everything is connected through a kernel in $this->app
- It doesn't matter if it's a shell or web browser launched code, everywhere is $this->app the same (unlike it's in Symfony2 for example, so you don't need to swear "why there is no $this->getDoctrine() available?!")
- Supports Unix-like configs in /etc which makes it a good framework for creating embedded applications
- Easy to use ORM, no entity managers, complicated structure, it's more simple
- Does not require to add components in different places to enable them (like it's in Symfony2)
- Easy mapping (eg. /Components/MyComponent/Class.php could be MyAppName\Components\MyComponent\Class)
- Deployment system, group tasks, full automation, on deploying a new developer environment you don't have to do anything but execute `deploy Build/Environment/Developer`
- Uses full potential of Unix environment, bases on powerful Unix tools executed from PHP code in deployment process (which makes requirement to install Cygwin/Bash on Windows)

Simple and clear divide into:
- Components (application's modules like "Translations", a group of classes)
- Classes (single classes that don't fit in components)
- Packages (contains controllers, templates, routing, and also Components and Classes)
- Schema (migrations, configs)
- Binaries (PF2 shell scripts)
- Tests (every application should contain automatic tests)

**There is a `PF2 shell` with commands similar to `php app/console` in Symfony, but a little bit more flexible and more clear.**

```bash
Welcome to Panthera Framework shell
Your project is localized at path: /path/to/project

Your application commands:
build-debian-package  install  shell

Panthera Framework 2 builtin commands:
deploy  docs  migrations  open-database  webserver
reload goto_app goto_fw welcome commands psysh
coveralls  phinx  php-parse  phpunit  psysh  test-reporter


Type "commands" to see list of available commands again any time
[damian|Panthera Framework|example-panthera-framework]$ deploy -m
Build/Database/ConfigurePhinx
Build/Database/Migrate
Build/Database/UpdateSystemMigrations
Build/Environment/Developer
Build/Environment/InstallComposer
Build/Environment/ShellConfiguration
Build/Environment/Webserver/Php
Build/Framework/AutoloaderCache
Build/Framework/UpdateComposerPackages
Build/Framework/Signals/UpdateSignalsIndex
Build/Release
Build/Release/Release
Build/Release/Upload
Build/Release/Version
Build/Release/Packaging/ArchLinux
Build/Routing/Cache
CodeInspection/ConvertTabsToSpaces
Create/Class
Create/Controller
Create/Entity
Create/Package
Tests
Tests/BuildTestDatabase
Tests/PHPUnitConfigure
Tests/PHPUnit
```



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
