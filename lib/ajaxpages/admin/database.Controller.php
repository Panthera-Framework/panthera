<?php
/**
 * Manage database
 *
 * @package Panthera\core\database\admin
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license GNU LGPLv3, see license.txt
 */
 
class databaseAjaxControllerCore extends pageController
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
    
    protected $permissions = array(
        'admin.databases' => array('Database management', 'database'),
    );
    
    /**
     * List all tables and check if it has own templates
     * 
     * @return null
     */
    
    public function debugTablesAction()
    {
        $tables = array();
        $t = $this -> panthera -> db -> listTables();
        $prefix = $this -> panthera -> config -> getKey('db_prefix');
        
        // search for database templates
        if ($t)
        {
            foreach ($t as $table)
            {
                $table = str_replace($prefix, '', $table);
                
                $tables[$table] = array(
                    'hasTemplate_mysql' => getContentDir('database/templates/' .$table. '.sql'),
                    'hasTemplate_sqlite3' => getContentDir('database/templates/sqlite3/' .$table. '.sql'),
                    'isInDB' => True,
                );
            }
        }

        // search for template files (alternative to find missing database tables)
        $search = array_merge(
            scandir(PANTHERA_DIR. '/database/templates'), // lib, mysql
            scandir(PANTHERA_DIR. '/database/templates/sqlite3') // lib, sqlite3
        );
        
        if (is_dir(SITE_DIR. '/content/database/templates'))
            $search = array_merge($search, scandir(SITE_DIR. '/content/database/templates'));
        
        if (is_dir(SITE_DIR. '/content/database/templates/sqlite3'))
            $search = array_merge($search, scandir(SITE_DIR. '/content/database/templates/sqlite3'));
        
        // remove all duplicates
        $search = array_unique($search);
        
        if ($search)
        {
            foreach ($search as $table)
            {
                if (pathinfo($table, PATHINFO_EXTENSION) !== 'sql')
                    continue;
                
                $table = str_replace('.sql', '', $table);
                
                if (!isset($tables[$table]))
                {
                    $tables[$table] = array(
                        'hasTemplate_mysql' => getContentDir('database/templates/' .$table. '.sql'),
                        'hasTemplate_sqlite3' => getContentDir('database/templates/sqlite3/' .$table. '.sql'),
                        'isInDB' => False,
                    );
                }
            }
        }

        if ($this -> panthera -> varCache)
        {
            if (!$this -> panthera -> varCache -> exists('database.schemas') or isset($_GET['forceUpdateCache']))
            {
                foreach ($tables as $tableName => &$table)
                {
                    if ($table['isInDB'])
                    {
                        try {
                            $a = new SQLStructure($this -> panthera -> db -> showCreateTable('{$db_prefix}' .$tableName));
                        } catch (Exception $e) { $table['parserError'][] = array('a', $e -> getMessage()); }
                        
                        // compare live database schema with MySQL template
                        if ($table['hasTemplate_mysql'])
                        {
                            try {
                                $mysqlDiff = new SQLStructure(file_get_contents($table['hasTemplate_mysql']));
                                $table['db_vs_mysql'] = $a -> compareWith($mysqlDiff, '{$db_prefix}' .$tableName);
                            } catch (Exception $e) { $table['parserError'][] = array('db_vs_mysql', $e -> getMessage()); }
                        }
                            
                            
                        // compare live database schema with SQLite3
                        if ($table['hasTemplate_sqlite3'])
                        {
                            try {
                                $sqlite3Diff = new SQLStructure(file_get_contents($table['hasTemplate_sqlite3']));
                                $table['db_vs_sqlite3'] = $a -> compareWith($sqlite3Diff, '{$db_prefix}' .$tableName);
                            } catch (Exception $e) { $table['parserError'][] = array('db_vs_sqlite3', $e -> getMessage()); }
                        }
    
                        try {
                            // compare two templates
                            if ($table['hasTemplate_mysql'] and $table['hasTemplate_sqlite3'])
                                $table['sqlite3_vs_mysql'] = $sqlite3Diff -> compareWith($mysqlDiff, '{$db_prefix}' .$tableName);

                        } catch (Exception $e) { $table['parserError'][] = array('sqlite3_vs_mysql', $e -> getMessage()); }
                    }
                }
                
                $this -> panthera -> varCache -> set('database.schemas', $tables);
            } else {
                $tables = $this -> panthera -> varCache -> get('database.schemas');
            }
        }

        $this -> getFeatureRef('admin.database.debugTables.tables', $tables);
        $this -> panthera -> template -> push('tables', $tables);        
        $this -> panthera -> template -> display('database.debugTables.tpl');
        pa_exit();
    }
    
    
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
        $this -> panthera -> locale -> loadDomain('database');
        $this -> dispatchAction();
        $this -> panthera -> template -> push('sql_attributes', $this->getAttributes());
        $this -> panthera -> template -> push('panthera_attributes', $this->getInternalAttributes());
        
        return $this -> panthera -> template -> compile('database.tpl');
    }
}
