<?php
namespace Panthera;

/**
 * Panthera Framework 2 index service class - get all project files
 *   validate content, help autoloader find appropriate classes
 *
 * @Package Panthera
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class indexService extends baseClass
{
    public $libIndex = array();
    public $appIndex = array();

    public $parser = null;

    /**
     * Main function indexing files from lib and application directory.
     *
     * @author Mateusz Warzyński
     * @return array
     */
    public function indexDirectoriesAndFiles()
    {
        $this->libIndex = $this->listIn($this->app->libPath, '', $this->app->libPath);
        $this->appIndex = $this->listIn($this->app->appPath, '', $this->app->appPath);
    }

    /**
     * Function lists all files recursively
     *
     * @param string $dir root directory to list files
     * @param string $prefix
     * @param string $mainDir constant root directory needed to validate path
     * @author Mateusz Warzyński
     * @return array
     */
    public function listIn($dir, $prefix = '', $mainDir = '')
    {
        $dir = rtrim($dir, '\\/');
        $result = array();

        foreach (scandir($dir) as $f)
        {
            if ($f !== '.' and $f !== '..')
            {
                if (is_dir("$dir/$f"))
                {
                    $result = array_merge($result, $this->listIn($dir. "/" .$f, $prefix.$f. "/", $mainDir));
                } else {
                    $result[realpath($mainDir. $prefix. $f)] = '';
                }
            }
        }

        // remove unnecessary files from result
        foreach ($result as $filePath => $none)
        {
            if (strpos($filePath, '/.git/') !== false || strpos($filePath, '/.idea/') !== false || strpos($filePath, '/.gitignore') !== false || strpos($filePath, '/vendor/'))
            {
                unset($result[$filePath]);
            }
        }

        return $result;
    }

    /**
     * Get classes from indexed files in lib root directory, required in autoloader
     *
     * @return array
     */
    public function indexClasses()
    {
        $result = array();

        if (empty($this->libIndex))
        {
            $this->indexDirectoriesAndFiles();
        }

        foreach (array_merge($this->libIndex, $this->appIndex) as $filePath => $none)
        {
            if (strpos(basename($filePath), '.class.php') !== false)
            {
                $classContent = file_get_contents($filePath);

                if ($classContent !== false)
                {
                    $result[$filePath] = $this->fileGetClasses($classContent);
                }
            }
        }

        return $result;
    }

    /**
     * Get list of declared class in PHP file (without including it)
     *
     * @param string $php_code
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @author AbiusX <http://stackoverflow.com/questions/7153000/get-class-name-from-file>
     * @return array
     */
    public static function fileGetClasses($php_code)
    {
        $classes = array();
        $namespace = '';
        $tokens = token_get_all($php_code);
        $count = count($tokens);

        for ($i=0; $i < $count; $i++)
        {
            if ($tokens[$i][0] === T_NAMESPACE)
            {
                for ($j=$i+1; $j<$count; ++$j)
                {
                    if ($tokens[$j][0]===T_STRING)
                    {
                        $namespace .= "\\" . $tokens[$j][1];

                    } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }
            if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_TRAIT)
            {
                for ($j=$i+1; $j<$count; ++$j)
                {
                    if ($tokens[$j] === '{' && !isset($classes[$namespace . "\\" . $tokens[$i + 2][1]]))
                    {
                        $classes[$namespace . "\\" . $tokens[$i + 2][1]] = true;
                    }
                }
            }
        }

        return array_keys($classes);
    }
}