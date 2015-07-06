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
     * Main function indexing files from lib and application directory.
     *
     * @author Mateusz Warzyński
     * @return array
     */
    public function indexDirectoriesAndFiles()
    {
        $libIndex = $this->listIn($this->app->libPath, '', $this->app->libPath);
        $appIndex = $this->listIn($this->app->appPath, '', $this->app->appPath);
        return array('lib' => $libIndex, 'app' => $appIndex);
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
    public function listIn($dir, $prefix = '', $mainDir = '') {
        $dir = rtrim($dir, '\\/');
        $result = array();

        foreach (scandir($dir) as $f) {
            if ($f !== '.' and $f !== '..') {
                if (is_dir("$dir/$f")) {
                    $result = array_merge($result, $this->listIn("$dir/$f", "$prefix$f/", $mainDir));
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
}