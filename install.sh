#!/bin/sh
curl -sS https://getcomposer.org/installer | php
php composer.phar install

echo "Creating shared libraries directory"
mkdir lib/share

echo "Moving FirePHP"
mv vendor/firephp/firephp-core lib/share/firephp-core --force

echo "Moving phpQuery"
mv vendor/electrolinux/phpquery/phpQuery/* lib/share/ --force

echo "Moving Facebook PHP SDK"
mv vendor/facebook/php-sdk lib/share/facebook-php-sdk --force

echo "Moving cron-expression"
mv vendor/mtdowling/cron-expression lib/share/cron-expression --force

echo "Moving httpful"
mv vendor/nategood/httpful lib/share/httpful --force

echo "Moving phpmailer"
mv vendor/phpmailer/phpmailer lib/share/phpmailer --force

echo "Moving RainTPLv3"
mv vendor/rain/raintpl lib/share/raintpl3  --force

echo "Moving password_compat"
mv vendor/ircmaxell/password-compat lib/share/password-compat --force

echo "Moving Mobile-Detect"
mv vendor/mobiledetect/mobiledetectlib lib/share/mobiledetectlib --force

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
