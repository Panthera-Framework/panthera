<?php
/**
  * Writable JSON file editor
  * 
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Writable JSON editor
  *
  * @package Panthera\installer
  * @author Damian Kęska
  */

class writableJSON
{
    protected $db = array();
    protected $file = '';
    protected $modified = False;

    /**
      * Constructor
      *
      * @param string $file path
      * @return void 
      * @author Damian Kęska
      */

    public function __construct ($file)
    {
        $panthera = pantheraCore::getInstance();
    
        if (!is_file($file))
        {
            throw new Exception('Cannot open file "' .$file. '", check read permissions');
        }
        
        $this -> file = $file;
        $this -> db = (array)json_decode(file_get_contents($file));
        $panthera -> add_option('session_save', array($this, 'save'));
    }
    
    /**
      * Get a key from json database
      *
      * @param $key name
      * @return object|null
      * @author Damian Kęska
      */
    
    public function get($key)
    {
        if (array_key_exists($key, $this->db))
        {
            if (is_array($this->db[$key]))
                return (object)$this->db[$key]; // return as stdclass object
            else
                return $this->db[$key]; // return object that is already a stdclass object
        }
        
        return null;
    }
    
    /**
      * Set a variable
      *
      * @param string $key
      * @param string $value
      * @return bool 
      * @author Damian Kęska
      */
    
    public function set($key, $value)
    {
        $oldValue = $this->db[$key];
    
        if (is_array($value))
            $this->db[$key] = (object)$value;
        else
            $this->db[$key] = $value;
            
        if ($oldValue != $value)
            $this->modified = True;
            
        return True;
    }
    
    /**
      * Remove a key from database
      *
      * @param string $key
      * @return bool 
      * @author Damian Kęska
      */
    
    public function remove($key)
    {
        if (isset($this->db[$key]))
        {
            unset($this->db[$key]);
            return True;
        }
        
        return False;
    }
    
    /**
      * Save data back to file
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function save()
    {
        if ($this->modified == True)
        {
            $fp = fopen($this->file, 'w');
            if (version_compare(phpversion(), '5.4.0', '>'))
                fwrite($fp, json_encode($this->db, JSON_PRETTY_PRINT));
            else
                fwrite($fp, json_encode($this->db));
                
            fclose($fp);
        }
    }
    
    public function __get($key) { return $this->get($key); }
    public function __set($key, $value) { return $this->set($key, $value); }
}
