<?php
/**
 * Manage database
 *
 * @package Panthera\core\database\admin
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license GNU LGPLv3, see license.txt
 */
 
class databaseAjaxControllerCore extends frontController
{
    protected $uiTitlebar = array(
        'Database management', 'database'
    );
    
    protected $attributes = array( "SERVER_INFO", "SERVER_VERSION", "AUTOCOMMIT", "ERRMODE", "CASE", "CLIENT_VERSION", "CONNECTION_STATUS",
        "ORACLE_NULLS", "PERSISTENT", "PREFETCH", "TIMEOUT"
    );
    
    protected $attributesEnglish = array( 'ERRMODE' => 'Error mode', 'CLIENT_VERSION' => 'Client version', 'CONNECTION_STATUS' => 'Connection status',
        'TIMEOUT' => 'Connection timeout', 'SERVER_INFO' => 'Server info', 'SERVER_VERSION' => 'Server version'
    );
    
    
    /**
     * Get PDO attributes about database
     * 
     * @author Mateusz Warzyński
     * @return array
     */
    
    protected function getAttributes()
    {
        $attributesTpl = array();
        
        foreach ($this->attributes as $attribute) {
            $name = $attribute;
        
            // user friendly names
            if (array_key_exists($attribute, $this->attributesEnglish))
                $name = $this->attributesEnglish[$attribute];
        
            try {
                $attributesTpl[] = array('name' => localize($name, 'database'),
                    'value' => $this->panthera->db->sql->getAttribute(constant("PDO::ATTR_".$attribute))
                );
            } catch (Exception $e) { /* pass */ }
        }
        
        return $attributesTpl;
    }


    /**
     * Get Panthera attributes about database
     * 
     * @author Mateusz Warzyński
     * @return array
     */
    
    protected function getInternalAttributes()
    {
        // internal Panthera driver attributes
        $pantheraAttributes = array();
        $pantheraAttributes[] = array('name' => localize('Socket type', 'database'), 'value' => $this->panthera->db->getSocketType());
        
        if ($this->panthera->config->getKey('db_timeout') != NULL)
            $pantheraAttributes[] = array('name' => localize('Connection timeout', 'database'), 'value' => $this->panthera->config->getKey('db_timeout'));
        else
            $pantheraAttributes[] = array('name' => localize('Connection timeout', 'database'), 'value' => '30');
        
        if ($this->panthera->config->getKey('db_emulate_prepares') != NULL)
            $pantheraAttributes[] = array('name' => localize('Emulate prepared queries', 'database'), 'value' => $this->panthera->config->getKey('db_emulate_prepares'));
        else
            $pantheraAttributes[] = array('name' => localize('Emulate prepared queries', 'database'), 'value' => false, 'type' => 'bool');
        
        if ($this->panthera->config->getKey('db_mysql_buffered_queries') != NULL)
            $pantheraAttributes[] = array('name' => localize('Buffered MySQL queries', 'database'), 'value' => $this->panthera->config->getKey('db_mysql_buffered_queries'));
        else
            $pantheraAttributes[] = array('name' => localize('Buffered MySQL queries', 'database'), 'value' => false, 'type' => 'bool');
            
        if ($this->panthera->config->getKey('db_autocommit') != NULL)
            $pantheraAttributes[] = array('name' => localize('Automatic commit mode', 'database'), 'value' => $this->panthera->config->getKey('db_autocommit'), 'type' => 'bool');
        else
            $pantheraAttributes[] = array('name' => localize('Automatic commit mode', 'database'), 'value' => false, 'type' => 'bool');
        
        if ($this->panthera->db->getSocketType() == 'mysql')
        {
            $pantheraAttributes[] = array('name' => localize('Server adress', 'database'), 'value' => $this->panthera->config->getKey('db_host'));
            $pantheraAttributes[] = array('name' => localize('Username', 'database'), 'value' => $this->panthera->config->getKey('db_username'));
            $pantheraAttributes[] = array('name' => localize('Database name', 'database'), 'value' => $this->panthera->config->getKey('db_name'));
            $pantheraAttributes[] = array('name' => localize('Prefix', 'database'), 'value' => $this->panthera->config->getKey('db_prefix'));
            
        } elseif ($this->panthera->db->getSocketType() == 'sqlite') {
            $pantheraAttributes[] = array('name' => localize('File', 'database'), 'value' => $this->panthera->config->getKey('db_file'));
        }
        
        return $pantheraAttributes;
    }
    
    
    /**
     * Displays results (everything is here)
     * 
     * @return string
     */
    
    public function display()
    {
        if (!getUserRightAttribute($this -> panthera -> user, 'can_manage_databases')) {
            $noAccess = new uiNoAccess; $noAccess -> display();
        }
        
        $this -> panthera -> locale -> loadDomain('database');
        
        $this -> panthera -> template -> push('sql_attributes', $this->getAttributes());
        $this -> panthera -> template -> push('panthera_attributes', $this->getInternalAttributes());
        
        return $this -> panthera -> template -> compile('database.tpl');
    }
}
?>
