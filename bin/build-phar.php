#!/usr/bin/env php
<?php
/**
 * This CLI script builds a phar archive that includes Panthera Framework /lib directory
 * 
 * After successful build Panthera Framework libraries could be included using "include 'phar://pantheralib.phar/boot.php';"
 * The best solution is to enter lib path in your app.php - phar://pantheralib.phar/ it should find and include everything needed
 * 
 * @package Panthera\bintools\build-phar
 * @author Damian Kęska
 */
 
$withoutShare = True;

include '.lib.php';

if (is_file('./pantheralib.phar'))
    unlink('./pantheralib.phar');

print("Panthera Framework libraries phar archive packager\n");

if (!is_writable('./'))
    die("Current directory ".getcwd()." is not writable!\n");

try {
    $phar = new Phar('./pantheralib.phar');
} catch (Exception $e) {
    die("Cannot open Phar archive: ".$e -> getMessage()."\n");
}

foreach (scandirDeeply('lib') as $path)
{
    $pharPath = substr($path, 4, strlen($path));
    
    if ($withoutShare and substr($pharPath, 0, 6) == 'share/')
        continue;
    
    print("Adding ".$pharPath."\n");
    $phar->addFile(realpath($path), $pharPath);
}

$phar->setStub($phar->createDefaultStub("boot.php"));

if ($withoutShare)
{
    print("Copying thirdparty libraries...\n");
    @mkdir('.pharoverlay');
    recurseCopy('lib/share', '.pharoverlay/share');
    print("NOTE: Copied thirdparty libraries to .pharoverlay/share directory, it must be placed in same directory as phar archive\n");
}

print("Created phar archive.\n");
