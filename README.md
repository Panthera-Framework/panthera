Panthera Framework 2
========

[![Code Climate](https://codeclimate.com/github/Panthera-Framework/panthera/badges/gpa.svg)](https://codeclimate.com/github/Panthera-Framework/panthera)
[![Test Coverage](https://codeclimate.com/github/Panthera-Framework/panthera/badges/coverage.svg)](https://codeclimate.com/github/Panthera-Framework/panthera/coverage)
[![Unit testing](https://travis-ci.org/Panthera-Framework/panthera.svg)](https://travis-ci.org/Panthera-Framework/panthera)

Assumptions for second version:

- Deployment
- Automatic code analysis to keep the project in single coding style
- Automatic tests
- Separate driver/orm code for SQLite3, MySQL databases
- Base code + separate support for RainTPL4 and a place for other templating engines
- Plug & Play modules
- Integration with Unix (incron and crontab to be automaticaly setup by application - a deployment job would be need here)
- Simpler admin panel
- Simpler modules for creating pages like "business cards" eg. data automaticaly loaded from Excel or PDF and displayed on page
that is formatted by a templating engine

## How to setup a developer environment

```
git clone https://github.com/Panthera-Framework/panthera

# install dependencies
cd panthera/lib
composer install

# deploy a developer environment, build indexes, caches, configure tools etc.
cd ../application/
../lib/bin/deploy build/environment/developer

# enter application's shell
./content/bin/shell
```

![screenshot](http://oi59.tinypic.com/2mypxr5.jpg)
