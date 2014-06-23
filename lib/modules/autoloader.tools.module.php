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
        if (is_object($data))
            $panthera = $data;
        else
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

        $modules = array_merge($modules, filesystem::scandirDeeply(PANTHERA_DIR. '/pages', True));
        $modules = array_merge($modules, filesystem::scandirDeeply(PANTHERA_DIR. '/ajaxpages', True));
        $modules = array_merge($modules, filesystem::scandirDeeply(SITE_DIR. '/content/pages', True));
        $modules = array_merge($modules, filesystem::scandirDeeply(SITE_DIR. '/content/ajaxpages', True));

        // list of classes and files to autoload
        $autoload = array();

        foreach ($modules as $moduleFile)
        {
            if(strpos($moduleFile, '.module.php') !== False)
            {
                $moduleName = str_ireplace(PANTHERA_DIR. '/modules/', '',
                              str_ireplace(SITE_DIR. '/content/modules/', '',
                              str_ireplace('.module.php', '', $moduleFile)));

            } else {
                if (strpos($moduleFile, '.Controller.php') === False)
                    continue;

                $moduleName = 'file:' .str_replace(PANTHERA_DIR, '{$PANTHERA_DIR}', str_replace(SITE_DIR, '{$SITE_DIR}', $moduleFile));
            }

            $classes = self::fileGetClasses($moduleFile);

            foreach ($classes as $className)
            {
                if(substr($className, 0, 1) == '\\')
                    $className = substr($className, 1);

                $autoload[$className] = $moduleName;
            }
        }

        $autoloadTmpLevels = array();

        // create aliases for classes (*Core, *System, *Override)
        foreach ($autoload as $className => $moduleName)
        {
            $found = False;

            // CORE
            if (substr($className, strlen($className)-4, 4) == 'Core')
            {
                $realClassName = substr($className, 0, strlen($className)-4); // without "Core"

                if (!isset($autoloadTmpLevels[$realClassName]) or $autoloadTmpLevels[$realClassName] < 1)
                {
                    unset($autoload[$className]);
                    $autoloadTmpLevels[$realClassName] = 1;
                    $found = True;
                }
            }

            // SYSTEM
            if (substr($className, strlen($className)-6, 6) == 'System')
            {
                $realClassName = substr($className, 0, strlen($className)-6);

                if (!isset($autoloadTmpLevels[$realClassName]) or $autoloadTmpLevels[$realClassName] < 2)
                {
                    unset($autoload[$className]);
                    $autoloadTmpLevels[$realClassName] = 2;
                    $found = True;
                }
            }

            // OVERRIDE
            if (substr($className, strlen($className)-8, 8) == 'Override')
            {
                $realClassName = substr($className, 0, strlen($className)-8);

                if (!isset($autoloadTmpLevels[$realClassName]) or $autoloadTmpLevels[$realClassName] < 8)
                {
                    unset($autoload[$className]);
                    $autoloadTmpLevels[$realClassName] = 3;
                    $found = True;
                }
            }

            if ($found)
                $autoload[$realClassName] = ':alias:' .$className. ':alias:' .$moduleName;
        }

        $autoload['panthera'] = ':alias:pantheraCore';

        //$panthera -> config -> setKey('autoloader', $autoload, 'array');
        $fp = fopen(SITE_DIR. '/content/tmp/autoloader.php', 'w');
        fwrite($fp, "<?php\n\$autoloader = " .var_export($autoload, true). ";\n");
        fclose($fp);

        $panthera -> autoloader = $autoload;

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
                {
                	if ($tokens[$j]==='{')
                        $classes[]=$namespace."\\".$tokens[$i+2][1];
                }
            }
        }
        return $classes;
    }
}