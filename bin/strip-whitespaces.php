#!/usr/bin/env php
<?php
include '.lib.php';

/**
 * Strip whitespaces from every line in file
 * 
 * @package Panthera\bintools\strip-whitespaces
 * @param string $path File path
 * @author Damian Kęska
 * @return int
 */

function stripWhiteSpaces($path)
{
    $array = file($path);
    $diff = 0;
    
    foreach ($array as &$line)
    {
        $copy = $line;
        $line = rtrim($line);
        
        if ($copy != $line)
            $diff++;
    }
    
    $fp = fopen($path, 'w');
    fwrite($fp, implode("\n", $array));
    fclose($fp);
    
    return $diff;
}

$dir = '../lib';

if (is_dir('./lib'))
    $dir = './lib';

$files = scandirDeeply($dir);

foreach ($files as $file)
{
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) != 'php')
        continue;
    
    $pathName = str_replace(array(
        '../', './',
    ), '', $file);
    
    if (strpos($file, 'lib/share/') !== False)
        continue;
    
    $diff = stripWhiteSpaces($file);
    print("stripWhiteSpaces(".$pathName.") - ".$diff." matches\n");
}

