#!/bin/sh
export COMPOSER_HOME="/tmp" # Fix for execution from PHP
curl -sS https://getcomposer.org/installer | php
php composer.phar install

echo "Creating shared libraries directory"
mkdir lib/share

echo "Moving FirePHP"
mv vendor/firephp/firephp-core lib/share/firephp-core --force
rm lib/share/firephp-core/examples -rf
rm lib/share/firephp-core/tests -rf
rm lib/share/firephp-core/workspace -rf

echo "Moving phpQuery"
mv vendor/electrolinux/phpquery/phpQuery/* lib/share/ --force

echo "Moving Facebook PHP SDK"
mv vendor/facebook/php-sdk lib/share/facebook-php-sdk --force

echo "Moving cron-expression"
mv vendor/mtdowling/cron-expression lib/share/cron-expression --force
rm lib/share/cron-expression/tests -rf
rm lib/share/facebook-php-sdk/tests -rf

echo "Moving httpful"
mv vendor/nategood/httpful lib/share/httpful --force

echo "Moving phpmailer"
mv vendor/phpmailer/phpmailer lib/share/phpmailer --force
rm lib/share/phpmailer/examples -rf
rm lib/share/phpmailer/docs -rf
rm lib/share/phpmailer/test -rf

echo "Moving RainTPLv3"
mv vendor/rain/raintpl lib/share/raintpl3  --force
rm lib/share/raintpl3/.git -rf
rm lib/share/raintpl3/example*
rm lib/share/raintpl3/templates -rf

echo "Moving password_compat"
mv vendor/ircmaxell/password-compat lib/share/password-compat --force
rm lib/share/password-compat/.git -rf

echo "Moving Mobile-Detect"
mv vendor/mobiledetect/mobiledetectlib lib/share/mobiledetectlib --force
rm lib/share/mobiledetectlib/tests -rf

echo "Moving mpdf"
mv vendor/mpdf/mpdf lib/share/mpdf --force
rm -rf lib/share/mpdf/examples
rm -rf lib/share/mpdf/.git

echo "Moving minify"
mv vendor/mrclay/minify lib/share/minify
rm -rf lib/share/minify/.git
rm -rf lib/share/minify/min_unit_tests
rm -rf lib/share/minify/min_extras

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
