<?php
/**
  * Panthera Installer core class
  *
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

class pantheraInstaller
{
    public $template = null;

    /**
      * Constructor
      *
      * @param panthera $panthera
      * @return void 
      * @author Damian Kęska
      */

    public function __construct($panthera)
    {
        $this -> panthera = $panthera;
        
        if (!($index = getContentDir('installer/config.json')))
        {
            throw new Exception('Cannot find /lib/installer/config.json (check Panthera installation integrity), and /lib/installer/config.json');
        }
        
        $panthera -> importModule('rwjson');
        
        if (!is_dir(SITE_DIR. '/content/installer'))
            mkdir(SITE_DIR. '/content/installer');
        
        if (!is_file(SITE_DIR. '/content/installer/db.json'))
        {
            $fp = fopen(SITE_DIR. '/content/installer/db.json', 'w');
            fwrite($fp, '');
            fclose($fp);
        }
        
        // merge webroot if not merged
        if (!is_dir(SITE_DIR. '/css') or !is_dir(SITE_DIR. '/js') or !is_dir(SITE_DIR. '/images'))
            $panthera -> template -> webrootMerge();
        
        // temporary database for installer
        $this -> config = (object)json_decode(file_get_contents($index));
        $this -> db = new writableJSON(SITE_DIR. '/content/installer/db.json');
    }
    
    /**
      * Display installer's template
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function display()
    {
        if (!$this->template)
            $this -> template = 'no_page';
    
        $this -> panthera -> template -> push ('stepTemplate', $this->template);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
            $this -> panthera -> template -> display ($this->template. '.tpl');
        else
            $this -> panthera -> template -> display('layout.tpl');
    }
    
}
