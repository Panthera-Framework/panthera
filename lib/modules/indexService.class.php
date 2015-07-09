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
    /**
     * Store lib files index
     *
     * @var array
     */
    public $libIndex = array();

    /**
     * Store application files index
     *
     * @var array
     */
    public $appIndex = array();

    /**
     * Store classes for autoloader
     *
     * @var array
     */
    public $classIndex = array();

    /**
     * Initialize index module
     *      check if cache contains indexed files
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->app->cache->get('indexFiles.lib') === null)
        {
            $this->indexFiles(true, false);
        }

        if ($this->app->cache->get('indexFiles.app') === null)
        {
            $this->indexFiles(false, true);
        }
    }

    /**
     * Main function which indexes files in lib and application root directory
     *
     * @param bool $lib if you want to index lib root directory set true
     * @param bool $app if you want to index application root directory set true
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return void
     */
    public function indexFiles($lib = true, $app = true)
    {
        if ($lib)
        {
            $this->libIndex = $this->listFiles($this->app->libPath, '', $this->app->libPath);
            $this->app->cache->set('indexFiles.lib', $this->libIndex);
        }

        if ($app)
        {
            $this->appIndex = $this->listFiles($this->app->appPath, '', $this->app->appPath);
            $this->app->cache->set('indexFiles.app', $this->appIndex);
        }
    }

    /**
     * Get classes from indexed files in lib root directory, required in autoloader
     *
     * @param bool $lib
     * @param bool $app
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return array
     */
    public function indexClasses($lib = true, $app = true)
    {
        $result = array();
        $arrayFiles = array();

        if (empty($this->libIndex) || empty($this->appIndex))
        {
            $this->indexFiles(true, true);
        }

        if ($lib && $app)
        {
            $arrayFiles = array_merge($this->libIndex, $this->appIndex);
        } elseif ($lib) {
            $arrayFiles = $this->libIndex;
        } elseif ($app) {
            $arrayFiles = $this->appIndex;
        }

        foreach ($arrayFiles as $filePath => $none)
        {
            if (strpos(basename($filePath), '.class.php') !== false)
            {
                $classContent = file_get_contents($filePath);

                if ($classContent !== false)
                {
                    $result[$filePath] = $this->getClassesFromCode($classContent);
                }
            }
        }

        return $result;
    }

    /**
     * Function lists all files recursively
     *
     * @param string $dir root directory to list files
     * @param string $prefix
     * @param string $mainDir constant root directory needed to validate path
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return array
     */
    public function listFiles($dir, $prefix = '', $mainDir = '')
    {
        $dir = rtrim($dir, '\\/');
        $result = array();

        foreach (scandir($dir) as $f)
        {
            if ($f !== '.' and $f !== '..')
            {
                if (is_dir("$dir/$f"))
                {
                    $result = array_merge($result, $this->listFiles($dir. "/" .$f, $prefix.$f. "/", $mainDir));
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
     * Get list of declared class in PHP file (without including it)
     *
     * @param string $phpCode
     * @author Damian Kęska <damian@pantheraframework.org>
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @author AbiusX <http://stackoverflow.com/questions/7153000/get-class-name-from-file>
     * @return array
     */
    public function getClassesFromCode($phpCode)
    {
        $classes = array();
        $namespace = '';
        $tokens = token_get_all($phpCode);
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

        $this->app->cache->set('indexFiles.classes', $classes);

        return array_keys($classes);
    }
}