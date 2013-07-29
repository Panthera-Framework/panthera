#!/bin/sh
curl -sS https://getcomposer.org/installer | php
php composer.phar install

echo "Creating shared libraries directory"
mkdir lib/share

echo "Moving FirePHP"
mv vendor/firephp/firephp-core lib/share/firephp-core

echo "Moving phpQuery"
mv vendor/electrolinux/phpquery/phpQuery/* lib/share/

echo "Moving Facebook PHP SDK"
mv vendor/facebook/php-sdk lib/share/facebook-php-sdk

echo "Moving cron-expression"
mv vendor/mtdowling/cron-expression lib/share/cron-expression

echo "Moving httpful"
mv vendor/nategood/httpful lib/share/httpful

echo "Moving phpmailer"
mv vendor/phpmailer/phpmailer lib/share/phpmailer

echo "Moving RainTPLv3"
mv vendor/rain/raintpl lib/share/raintpl3 

echo "Moving password_compat"
mv vendor/ircmaxell/password-compat lib/share/password-compat

# This is temporary while waiting for RainTPLv3 author to merge pull requests with fixes required Panthera to work
echo "Cloning Panthera-Framework/raintpl3 fork of RainTPLv3"
rm -rf lib/share/raintpl3
git clone https://github.com/Panthera-Framework/raintpl3.git -b split_parser lib/share/raintpl3

# clean up
echo "Cleaning up..."
rm vendor -rf
rm composer.phar
rm composer.lock
echo "Instalation done."
