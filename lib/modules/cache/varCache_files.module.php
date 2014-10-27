<?php
/**
  * Filesystem based cache
  *
  * @package Panthera\core\cache
  * @author Damian Kęska
  * @license GNU Lesser General Public License 3, see license.txt
  */

/**
  * Filesystem based cache
  *
  * @package Panthera\core\cache
  */

class varCache_files extends pantheraClass
{
    public $name = 'files';
    public $type = 'files';
    public $cacheDir = '';
    protected $memory = array();
    protected $indexEnabled = TRUE;
    protected $indexInterval = 3600; // every 1 hour (depends on traffic)
    protected $indexMaxSize = 2048; // 2 kbytes, scale this value to optimize performance

    /**
     * Constructor
     *
     * @return null
     */

    public function __construct ($panthera, $sessionKey='')
    {
        parent::__construct();
        
        $this -> cacheDir = 'tmp/cache';
        $this -> indexEnabled = 3600;
        $this -> indexInterval = null;
        
        if ($panthera -> config)
        {
            $this -> cacheDir = $panthera -> config -> getKey('varcache_files.dir', 'tmp/cache', 'string');
            $this -> indexEnabled = (bool)$panthera -> config -> getKey('varcache_files.index', 3600, 'int');
            $this -> indexInterval = $panthera -> config -> getKey('varcache_files.index');
        }
        
        if (substr($this -> cacheDir, 0, 1) !== '/')
            $this -> cacheDir = SITE_DIR. '/content/' .$this -> cacheDir;

        if (!is_dir($this -> cacheDir) && !mkdir($this->cacheDir))
            throw new Exception('Cannot create cache directory in "' .$this -> cacheDir. '"', 31381);

        if (!is_writable($this->cacheDir) || !@chmod($this->cacheDir, 0775))
            throw new Exception('Cache directory "' .$this->cacheDir. '" is not writable!', 31382);

        // cleanup indexed files
        if ($this->indexEnabled)
        {
            if (intval($this -> get('next.index.cleanup'))+$this->indexInterval < time())
            {
                $this -> set('next.index.cleanup', time()+$this->indexInterval, -1);

                if (!is_file($this -> cacheDir. '/index.phps'))
                {
                    $fp = fopen($this -> cacheDir. '/index.phps', 'w');
                    fwrite($fp, '');
                    fclose($fp);
                }

                // read index file into array
                $index = file($this -> cacheDir. '/index.phps');

                // truncate index file
                $fp = fopen($this -> cacheDir. '/index.phps', 'w');
                fwrite($fp, '');
                fclose($fp);

                foreach ($index as $record)
                {
                    // first is cache name, second is expiration
                    $exp = explode(' ', $record);
                    $file = $this->cacheDir. '/' .substr($exp[0], 0, 3). '/' .$exp[0]. '.phps';

                    if (!is_file($file))
                        continue;

                    if (filemtime($file) < intval($exp[1]))
                    {
                        if (!is_writable($file))
                        {
                            if ($panthera && $panthera -> logging)
                                $panthera -> logging -> output('Cannot delete cached file "' .$file. '"', 'varCache');
                            
                            continue;
                        }
                        
                        
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Get entry from cache
     *
     * @param string $variable Variable name
     * @return mixed|null Returns data on success or null on failure
     */

    public function get($variable)
    {
        $cacheName = substr(hash('md4', $variable), 0, 10);
        $cacheDir = $this->cacheDir. '/' .substr($cacheName, 0, 3);

        if (!is_file($cacheDir. '/' .$cacheName. '.phps'))
            return null;

        if (!isset($this -> memory[$cacheName]))
            $unpacked = unserialize(file_get_contents($cacheDir. '/' .$cacheName. '.phps'));
        else
            $unpacked = $this -> memory[$cacheName];

        $unpacked['expiration'] = intval($unpacked['expiration']);

        if ($unpacked['expiration'] < time() and $unpacked['expiration'] !== -1)
        {
            $this -> remove($variable);
            return null;
        }

        $this -> memory[$cacheName] = $unpacked;

        return $unpacked['data'];
    }

    /**
     * Check if key exists in cache
     *
     * @param string $variable Variable name
     * @return bool
     */

    public function exists($variable)
    {
        $cacheName = substr(hash('md4', $variable), 0, 10);
        $cacheDir = $this->cacheDir. '/' .substr($cacheName, 0, 3);

        if (!is_file($cacheDir. '/' .$cacheName. '.phps'))
            return FALSE;

        return TRUE;
    }

    /**
     * Set a variable
     *
     * @param string $variable Variable name
     * @param mixed $value Value
     * @param int $expiration Expiration time in seconds eg. 60 = 1 minute
     */

    public function set($variable, $value, $expiration=-1)
    {
        if(!is_int($expiration) and $expiration)
            $expiration = $this -> panthera -> getCacheTime($expiration);

        $cacheName = substr(hash('md4', $variable), 0, 10);
        $cacheDir = $this->cacheDir. '/' .substr($cacheName, 0, 3);

        if (!is_dir($cacheDir))
            mkdir($cacheDir);

        if ($expiration > 0)
            $expiration = time()+$expiration;

        $array = array(
            'expiration' => $expiration,
            'data' => $value,
        );

        $this -> memory[$cacheName] = $array;

        $fp = fopen($cacheDir. '/' .$cacheName. '.phps', "w");

        if(flock($fp, LOCK_EX))
        {
            ftruncate($fp, 0);
            fwrite($fp, serialize($array));

            fflush($fp);
            flock($fp, LOCK_UN);
            fclose ($fp);

            if ($expiration !== -1)
                $this -> writeIndex($cacheName, $expiration);

            return TRUE;
        }

        if ($expiration !== -1)
            $this -> writeIndex($cacheName, $expiration);

        @fclose ($fp);
        return FALSE;
    }

    /**
     * Write expiration time to index file
     *
     * @param string $cacheName Hashed cache name
     * @param int $expiration Expiration time as unix timestamp in seconds
     */

    protected function writeIndex($cacheName, $expiration)
    {
        if (!$this->indexEnabled)
            return FALSE;

        $fp = fopen($this -> cacheDir. '/index.phps', 'a');

        if ($fp)
        {
            fwrite($fp, $cacheName. " " .$expiration. "\n");
            fclose($fp);
        }

        return TRUE;
    }

    /**
     * Remove a variable from cache
     *
     * @param string $variable Variable name
     * @return bool
     */

    public function remove($variable)
    {
        $cacheName = substr(hash('md4', $variable), 0, 10);
        $cacheDir = $this->cacheDir. '/' .substr($cacheName, 0, 3);

        unset($this -> memory[$cacheName]);

        if (is_file($cacheDir. '/' .$cacheName. '.phps'))
            @unlink($cacheDir. '/' .$cacheName. '.phps');

        return TRUE;
    }

    /**
     * Clear all keys
     *
     * @return bool
     */

    public function clear()
    {
        $this -> panthera -> importModule('filesystem');

        $this -> memory = array();
        $dirs = filesystem::scandirDeeply($this->cacheDir, False);

        $i = 0;

        foreach ($dirs as $dir)
        {
            $i++;

            if ($i == 1)
                continue;

            if (is_dir($dir))
                rmdir($dir);
            else
                @unlink($dir);
        }

        return TRUE;
    }
}