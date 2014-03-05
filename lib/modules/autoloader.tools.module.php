<?php
/**
  * Panthera autoloader tools
  * 
  * @package Panthera\modules\autoloader.tools
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Rebuild autoloader database
  *
  * @package Panthera\modules\autoloader.tools
  * @author Damian Kęska
  */
  
class pantheraAutoloader
{
    /**
      * Panthera autoloader cache update cronjob
      *
      * @param string $data
      * @return mixed 
      * @author Damian Kęska
      */

    public static function updateCache($data='')
    {
        $panthera = pantheraCore::getInstance();
        $panthera -> importModule('filesystem');
        $panthera -> logging -> startTimer();
        
        $modules = filesystem::scandirDeeply(PANTHERA_DIR. '/modules', True);
        
        if (is_dir(SITE_DIR. '/content/modules'))
        {
            $modulesContent = filesystem::scandirDeeply(SITE_DIR. '/content/modules');
            
            if (is_array($modulesContent))
            {
                $modules = array_merge($modules, $modulesContent);
            }
        }
        
        // list of classes and files to autoload
        $autoload = array();
        
        foreach ($modules as $moduleFile)
        {
            if(stripos($moduleFile, '.module.php') === False)
            {
                continue;
            }
        
            $moduleName = str_ireplace(PANTHERA_DIR. '/modules/', '', 
                          str_ireplace(SITE_DIR. '/content/modules/', '', 
                          str_ireplace('.module.php', '', $moduleFile)));
                          
            $classes = self::fileGetClasses($moduleFile);
            
            foreach ($classes as $className)
            {
                if(substr($className, 0, 1) == '\\')
                {
                    $className = substr($className, 1);
                }
            
                $autoload[$className] = $moduleName;
            }
        }
        
        $panthera -> config -> setKey('autoloader', $autoload, 'array');
        $panthera -> logging -> output ('Updated autoloader cache, counting ' .count($autoload). ' elements', 'pantheraAutoLoader');
        
        return $autoload;
    }

    /*
     * Get list of cached classes
     * 
     * @return array
     */

    public static function getClasses()
    {
        $panthera = pantheraCore::getInstance();
        return $panthera -> config -> getKey('autoloader');
    }
    
    /**
      * Get list of declared class in PHP file (without including it)
      *
      * @param string $fileName
      * @return array 
      * @author AbiusX <http://stackoverflow.com/questions/7153000/get-class-name-from-file>
      */
    
    public static function fileGetClasses($fileName)
    {
        $php_code = file_get_contents ( $fileName );
        $classes = array ();
        $namespace="";
        $tokens = token_get_all ( $php_code );
        $count = count ( $tokens );

        for($i = 0; $i < $count; $i ++)
        {
            if ($tokens[$i][0]===T_NAMESPACE)
            {
                for ($j=$i+1;$j<$count;++$j)
                {
                    if ($tokens[$j][0]===T_STRING)
                        $namespace.="\\".$tokens[$j][1];
                    elseif ($tokens[$j]==='{' or $tokens[$j]===';')
                        break;
                }
            }
            if ($tokens[$i][0]===T_CLASS)
            {
                for ($j=$i+1;$j<$count;++$j)
                    if ($tokens[$j]==='{')
                    {
                        $classes[]=$namespace."\\".$tokens[$i+2][1];
                    }
            }
        }
        return $classes;
    }
}
