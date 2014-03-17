<?php
/**
  * Provides simple interface for cloning from SCM repositories
  *
  * @package Panthera\modules\scm
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

$panthera = pantheraCore::getInstance();

/**
  * Remote repository cloning functions
  *
  * @package Panthera\modules\scm
  * @author Damian Kęska
  */

class scm
{
    /**
      * Clone a repository branch
      *
      * @param string $url address to remote repository
      * @param string $destination where to clone all files
      * @param string $branch name to clone
      * @return bool 
      * @author Damian Kęska
      */

    public static function cloneBranch ($url, $destination, $branch='master')
    {
        $panthera = pantheraCore::getInstance();
        $panthera -> importModule('filesystem');
        
        // convert links in non-url format, eg. git urls without ssh:// protocol
        if (substr($url, -4) == '.git' and substr($url, 0, 6) != 'ssh://')
        {
            $gitSlashPos = strpos($url, ':');
            $url[$gitSlashPos] = '/'; // replace ":" with / to convert from git to http url
            $url = substr($url, 0, -4); // remove .git extension
            $url = 'http://' .$url;
        }
        
        // if its ssh:// protocol
        if (substr($url, -4) == '.git')
        {
            $url = substr($url, 0, -4);
        }
        
        $parsedUrl = parse_url($url);
        $domain = strtolower(str_ireplace('www.', '', $parsedUrl['host']));
        
        if ($domain == 'github.com')
        {
            return self::githubClone($url, $branch, $parsedUrl, $destination);
        } elseif ($domain == 'bitbucket.org') {
            return self::bitbucketClone($url, $branch, $parsedUrl, $destination);
        }
        
        $panthera -> logging -> output ('No supported scm site found', 'scm');
        
        return False;
    }
    
    /**
      * Clone a bitbucket.org hosted repository
      *
      * @param string $url address
      * @param string $branch name
      * @param array $parsedUrl result of parse_url($url)
      * @param string $destination directory
      * @return bool 
      * @author Damian Kęska
      */
    
    protected static function bitbucketClone($url, $branch, $parsedUrl, $destination)
    {
        // example zip tarball: https://bitbucket.org/thilina/icehrm-opensource/get/default.zip
        // example project: https://bitbucket.org/thilina/icehrm-opensource
        
        $panthera = pantheraCore::getInstance();
        $exp = explode('/', $parsedUrl['path']);
        
        if (count($exp) < 2)
        {
            $panthera -> logging -> output ('Invalid bitbucket.org url format', 'scm');
            return False;
        }
        
        // archive download
        $url = 'https://bitbucket.org/' .$exp[1]. '/' .$exp[2]. '/get/' .$branch. '.zip';
        $localFilePath = SITE_DIR. '/content/tmp/' .md5($url). '.zip';
        
        if (!self::downloadArchive($url, $localFilePath))
        {
            return False;
        }
        
        try {
             if (is_dir($destination))
                filesystem::deleteDirectory($destination);
        
            $panthera -> logging -> output ('Unpacking zipped archive', 'scm');
            $zip = new ZipArchive;
            $zip -> open($localFilePath);
            $zip -> extractTo($destination);
            $zip -> close();
            
            // clean up
            unlink($localFilePath);
            
            $files = scandir($destination);
            $dirName = end($files);
            
            if ($dirName == '.' or $dirName == '..')
            {
                $panthera -> logging -> output ('Empty git repository', 'scm');
                rmdir($destination); // clean up
                return False;
            }
            
            // do a directory move
            $destinationTmp = str_ireplace(basename($destination), basename($destination). '-tmp', $destination);
            
            if (is_dir($destinationTmp))
                filesystem::deleteDirectory($destinationTmp);
            
            rename($destination, $destinationTmp); // rename destination directory to $dirName-tmp
            rename($destinationTmp. '/' .$dirName, $destination); // move $repoName-$branch directory to destination
            rmdir($destinationTmp); // clean up temporary directory
            $panthera -> logging -> output ('Repository ' .$exp[1]. '/' .$exp[2]. ':' .$branch. ' cloned to "' .$destination. '"', 'scm');
            
            return True;
        } catch (Exception $e) {
            $panthera -> logging -> output ('Cannot unpack archive from bitbucket repository "' .$url. '", exception: ' .$e->getMessage(), 'scm');
            unlink($localFilePath); // clean up
            return False;
        }
        
    }
    
    /**
      * Clone a repository from github.com using zipped tarball
      *
      * @param string $url address
      * @param string $branch name
      * @param array $parsedUrl result of parse_url($url)
      * @param string $destination directory
      * @return bool 
      * @author Damian Kęska
      */
    
    protected static function githubClone($url, $branch, $parsedUrl, $destination)
    {
        $panthera = pantheraCore::getInstance();
        $exp = explode('/', $parsedUrl['path']);

        if (count($exp) < 2)
        {
            $panthera -> logging -> output ('Invalid github.com url format', 'scm');
            return False;
        }
        
        $url = 'https://github.com/' .$exp[1]. '/' .$exp[2]. '/archive/' .$branch. '.zip';
        $localFilePath = SITE_DIR. '/content/tmp/' .md5($url). '.zip';
        
        if (!self::downloadArchive($url, $localFilePath))
        {
            return False;
        }
        
        // unpack file
        try {
            if (is_dir($destination))
                filesystem::deleteDirectory($destination);
        
            $panthera -> logging -> output ('Unpacking zipped archive', 'scm');
            
            $zip = new ZipArchive;
            $zip -> open($localFilePath);
            $zip -> extractTo($destination);
            $zip -> close();
            
            // clean up
            unlink($localFilePath);
            
            // do a directory move
            $destinationTmp = str_ireplace(basename($destination), basename($destination). '-tmp', $destination);
            
            if (is_dir($destinationTmp))
                filesystem::deleteDirectory($destinationTmp);
            
            rename($destination, $destinationTmp); // rename destination directory to $dirName-tmp
            rename($destinationTmp. '/' .$exp[2]. '-' .$branch, $destination); // move $repoName-$branch directory to destination
            rmdir($destinationTmp); // clean up temporary directory
            $panthera -> logging -> output ('Repository ' .$exp[1]. '/' .$exp[2]. ':' .$branch. ' cloned to "' .$destination. '"', 'scm');
            
            return True;
        } catch (Exception $e) {
            $panthera -> logging -> output ('Cannot unpack archive from github repository "' .$url. '", exception: ' .$e->getMessage(), 'scm');
            unlink($localFilePath); // clean up
            return False;
        }
    }
    
    /**
      * Download archive (handles even big packages because of buffering)
      *
      * @param string $url
      * @param string $destination
      * @return bool 
      * @author Damian Kęska
      */
    
    protected static function downloadArchive($url, $destination)
    {
        $panthera = pantheraCore::getInstance();
        $panthera -> logging -> output('Downloading archive from "' .$url. '"', 'scm');
        
        $context = stream_context_create(array('http'=> array('timeout' => 25)));
        $remoteFile = @fopen($url, 'r', false, $context);
        
        if (!$remoteFile)
        {
            $panthera -> logging -> output ('Invalid url - repository or branch does not exists', 'scm');
            return False;
        }
        
        $localFile = @fopen($destination, 'wb');
        
        if (!$localFile)
        {
            $panthera -> logging -> output ('Cannot write to "' .$destination. '", check permissions', 'scm');
            return False;
        }
        
        while ($data = @fread($remoteFile, 8096))
        {
            @fwrite($localFile, $data);
        }
        
        @fclose($remoteFile);
        @fclose($localFile);
        
        if (!is_file($destination))
        {
            return False;
        }
        
        return True;
    }
}
