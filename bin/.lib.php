<?php
/**
 * Standalone filesystem utils extracted from Panthera Framework for CLI usage
 * 
 * @package Panthera\bintools
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Recursive directories scanning
 *
 * @param string $dir Directory
 * @param bool $filesOnly Show only files?
 * 
 * @package Panthera\bintools
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
 * @package Panthera\bintools
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