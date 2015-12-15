<?php
namespace Panthera\Components\Indexing;

use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Panthera Framework 2 index service class - get all project files
 *   validate content, help autoloader find appropriate classes
 *
 * @package Panthera\Components\Indexing
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class IndexService extends BaseFrameworkClass
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
     * Store mixed structure of $libIndex and $appIndex
     *
     * @var array
     */
    public $mixedFilesStructure = array();

    /**
     * Store classes for autoloader
     *
     * @var array
     */
    public $classIndex = array();

    /**
     * Main function which indexes files in lib and application root directory
     *
     * @param bool $lib if you want to index Panthera Framework libraries root directory set true
     * @param bool $app if you want to index application root directory set true
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public function indexFiles($lib = true, $app = true)
    {
        if ($lib)
        {
            $this->libIndex = array();
            static::listFiles($this->app->libPath, '', $this->app->libPath, $this->libIndex);
            ksort($this->libIndex);
        }

        if ($app)
        {
            $this->appIndex = array();
            static::listFiles($this->app->appPath. '/.content/', '', $this->app->appPath. '/.content/', $this->appIndex);
            ksort($this->appIndex);
        }

        $this->mixedFilesStructure = array_merge_recursive($this->libIndex, $this->appIndex);

        return array(
            'pantheraLibraries' => $this->libIndex,
            'application' => $this->appIndex,
            'mixed' => $this->mixedFilesStructure,
        );
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
    public static function listFiles($dir, $prefix = '', $mainDir = '', &$flatArray)
    {
        $mainDir = realpath($mainDir);
        $dir = rtrim($dir, '\\/');
        $result = array();

        foreach (scandir($dir) as $f)
        {
            if ($f == '.' || $f == '..')
            {
                continue;
            }

            $rPath = realpath($mainDir. "/" .$prefix. "/" .$f);
            $relativePath = substr($rPath, strlen($mainDir));
            $rDirPath = dirname($rPath);

            if (strpos($rPath, '/.git/') !== false || strpos($rPath, '/.idea/') !== false || strpos($rPath, '/.gitignore') !== false)
            {
                continue;
            }

            if (is_dir($rPath))
            {
                $flatArray[substr($rPath, strlen($mainDir))] = static::listFiles($dir. "/" .$f, $prefix.$f. "/", $mainDir, $flatArray);

            } else {
                $result[substr($rPath, strlen($mainDir))] = '';
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
    public static function getClassesFromCode($phpCode)
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
            if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_TRAIT || $tokens[$i][0] === T_INTERFACE)
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

    /**
     * Write to applicationIndex.php cache file
     *
     * @param string $entry Array index name
     * @param mixed $value Value to put
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public function writeIndexFile($entry, $value)
    {
        if (is_file($this->app->appPath. '/.content/cache/applicationIndex.php'))
        {
            require $this->app->appPath . '/.content/cache/applicationIndex.php';

        } else {
            $appIndex = array();
        }

        $appIndex[$entry] = $value;

        $filePointer = fopen($this->app->appPath. '/.content/cache/applicationIndex.php', 'w');
        fwrite($filePointer, "<?php\n\$appIndex = " .var_export($appIndex, true). ";");
        fclose($filePointer);

        return $appIndex;
    }
}