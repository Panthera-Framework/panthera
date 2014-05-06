<?php
/**
  * Writable JSON file editor
  * 
  * @package Panthera\modules\rwjson
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Writable JSON editor
  *
  * @package Panthera\modules\rwjson
  * @author Damian Kęska
  */

class writableJSON
{
    protected $db = array();
    protected $file = '';
    protected $modified = False;
    protected $readOnly = False;

    /**
      * Constructor
      *
      * @param string $file path
      * @param array|object|string $callback File or array of keys to include
      * @return void 
      * @author Damian Kęska
      */

    public function __construct ($file, $fallback=null)
    {
        $panthera = pantheraCore::getInstance();
    
        if (!is_file($file) or !is_readable($file))
        {
            throw new Exception('Cannot open file "' .$file. '", check read permissions');
        }
        
        $this -> file = $file;
        $this -> db = array();
        
        // merge fallback json into database
        if ($fallback)
        {
            if (is_object($fallback))
                $fallback = (array)$fallback;
            elseif (is_string($fallback) and is_file($fallback) and is_readable($fallback))
                $fallback = json_decode(file_get_contents($fallback), true);
            
            if (is_array($fallback))
            {
                $this -> db = array_merge($this -> db, $fallback);
                $panthera -> logging -> output('Merged ' .count($fallback). ' entries from fallback', 'rwjson');
            }
        }
        
        $this -> db = array_merge($this -> db, (array)json_decode(file_get_contents($file)));
        
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
     * List all keys
     * 
     * @author Damian Kęska
     * @return array
     */
    
    public function listAll()
    {
        return $this -> db;
    }
    
    /**
     * Check if key exists
     * 
     * @return bool
     */
    
    public function exists($key)
    {
        return isset($this -> db[$key]);
    }
    
    /**
     * Check if current object was modified
     * 
     * @author Damian Kęska
     * @return bool
     */
    
    public function isModified()
    {
        return $this -> modified;
    }
    
    /**
     * Set current object to read-only or read-write
     * 
     * @param bool $ro Set to true to set read-only, set to false to set to read-write
     * @author Damian Kęska
     * @return null
     */
    
    public function readOnly($ro=True)
    {
        $this -> readOnly = (bool)$ro;
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
        if ($this->modified == True and !$this -> readOnly)
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
