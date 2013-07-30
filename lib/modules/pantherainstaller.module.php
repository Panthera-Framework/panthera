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
        
        if (!($index = getContentDir('installer.json')))
        {
            throw new Exception('Cannot find /lib/installer.json (check Panthera installation integrity), and /content/installer.json');
        }
        
        $panthera -> importModule('rwjson');
        
        if (!is_file(SITE_DIR. '/content/installer.db.json'))
        {
            $fp = fopen(SITE_DIR. '/content/installer.db.json', 'w');
            fwrite($fp, '');
            fclose($fp);
        }
        
        // temporary database for installer
        $this -> config = (object)json_decode(file_get_contents($index));
        $this -> db = new writableJSON(SITE_DIR. '/content/installer.db.json');
    }
    
}
