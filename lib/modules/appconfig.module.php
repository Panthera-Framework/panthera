<?php
/**
  * Module that parses app.php and extracts $config variable for edition
  *
  * @package Panthera\modules\appconfig
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * App $config editor
  *
  * @package Panthera\modules\appconfig
  * @author Damian Kęska
  */

class appConfigEditor
{
    protected $originalConfig = array();
    protected $exported = '';
    protected $path = '';
    protected $app = '';
    public $config = array();

    /**
      * Load $config array from app.php, and parse to array
      *
      * @param string $path (optional path to app.php)
      * @return void
      * @author Damian Kęska
      */

    public function __construct($path='')
    {
        if ($path == '')
            $path = SITE_DIR. '/content/app.php';

        if (!is_file($path))
            throw new Exception('Cannot find "' .$path. '" file, please check permissions');

        $this -> path = $path;
        $this->app = file_get_contents($path);
        $this->exported = substr($this->app, strpos($this->app, '$config'), strpos($this->app, ');')-4);
        @eval($this->exported); // i know this is dangerous, but the app.php should be trusted enough to eval code from it

        if (!is_array($config))
        {
            throw new Exception('Syntax error occured while reading the file, or it does not have $config array set');
        }

        $this -> config = $config;
        $this -> originalConfig = $config;
    }

    /**
      * Save changes back to file
      *
      * @return bool
      * @author Damian Kęska
      */

    public function save()
    {
        // if variable was modified
        if ($this -> config != $this -> originalConfig and $this->path != '')
        {
            $app = str_replace($this->exported, '$config = ' .var_export((array)$this->config, True). ';', $this->app);
            $fp = @fopen($this->path, 'w');

            if (!$fp)
                throw new Exception('Cannot save "' .$this->path. '" file, please check write permissions');

            fwrite($fp, $app);
            @fclose($fp);

            return True;
        }

        return False;
    }
}