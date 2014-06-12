#!/usr/bin/env php
<?php
/**
 * This CLI script builds a phar archive that includes Panthera Framework /lib directory
 * 
 * After successful build Panthera Framework libraries could be included using "include 'phar://pantheralib.phar/boot.php';"
 * The best solution is to enter lib path in your app.php - phar://pantheralib.phar/ it should find and include everything needed
 * 
 * @author Damian Kęska
 */
 
$withoutShare = True;

/**
 * Recursive directories scanning
 *
 * @param string $dir Directory
 * @param bool $filesOnly Show only files?
 * 
 * @author Damian Kęska
 * @return string
 */

function scandirDeeply($dir, $filesOnly=True)
{
    $files = scandir($dir);
    $list = array();

    if (!$filesOnly)
        $list[] = $dir;

    foreach ($files as $file)
    {
        if ($file == ".." or $file == ".")
            continue;
            
        if (is_link($dir. '/' .$file))
        {
            if (in_array(readlink($dir. '/' .$file), $list))
                 continue;
        }

        if (is_file($dir. '/' .$file) or is_link($dir. '/' .$file)) {
            $list[] = $dir. '/' .$file;   
            
        } else {
                    
            $dirFiles = scandirDeeply($dir. '/' .$file, $filesOnly);

            foreach ($dirFiles as $dirFile)
                $list[] = $dirFile;
        }
                
    }

    return $list;
}

/**
 * Make a recursive copy of a directory
 *
 * @see http://stackoverflow.com/questions/9835492/move-all-files-and-folders-in-a-folder-to-another
 * @param string $src
 * @param string $dst
 * 
 * @author Baba
 * @return void
 */

function recurseCopy($src, $dst) 
{ 
    $dir = opendir($src); 
    @mkdir($dst); 
        
    while (false !== ($file = readdir($dir)))
    { 
        if ($file != '.' and $file != '..')
        { 
            if (is_dir($src . '/' . $file)) 
                recurseCopy($src . '/' . $file,$dst . '/' . $file); 
            else
                copy($src . '/' . $file,$dst . '/' . $file);  
        } 
    }
         
    closedir($dir);
} 

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
