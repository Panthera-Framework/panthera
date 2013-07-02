<?php
/**
  * Memcached custom wrapper
  *
  * @package Panthera\modules\memcached
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * A simple extension to Memcached standard class
  * it connects automaticaly to servers specified in Panthera configuration
  *
  * @package Panthera\modules\memcached
  * @author Damian Kęska
  */
  
class pantheraMemcached extends Memcached
{
    protected $panthera;

    /**
      * Connect to servers specified in Panthera configuration
      *
      * @param object $panthera
      * @return void 
      * @author Damian Kęska
      */

    public function __construct($panthera)
    {
        parent::__construct();
        $this->panthera = $panthera;
    
        // connect to servers specified in Panthera configuration
        $servers = $panthera -> config -> getKey('memcached_servers', array('default' => array('localhost', 11211, 50)), 'array');
        
        foreach ($servers as $server)
        {
            // host, port, weight
            $this -> addServer($server[0], $server[1], $server[2]);
        }
    }
}  
