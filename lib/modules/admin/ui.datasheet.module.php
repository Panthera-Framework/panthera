<?php
/**
  * Admin UI: Datasheet - drawing managed data tables
  * 
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Admin UI: Datasheet - drawing managed data tables (core class)
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */
  
class uiDatasheet extends pantheraClass
{
    public $idColumn = '';
    public $header = array();
    public $skipColumnsCountCheck = False;
    public $deleteButtons = False;
    public $editButtons = False;
    public $tableID = '';
    protected $data = array();
    protected $pagerID = '';
    protected $actions = array('new', 'edit', 'remove', 'editForm');
    
    /**
     * Constructor
     * 
     * @param string $tableID Table unique identifier
     * @return object
     */
    
    public function __construct($tableID='')
    {
        parent::__construct();
        
        $this -> tableID = $tableID;
        
        if (!$tableID)
            $this -> tableID = generateRandomString(6);
    }
    
    /**
     * Integrate ui.Pager with ui.Datasheet
     * 
     * @param uiPager $object Instance of uiPager class
     * @return null
     */
    
    public function adduiPager(uiPager $object)
    {
        $this -> pagerID = $object -> getName();
    }
    
    /**
     * Set primary key column that identifies every record (for sorting, deleting, editing etc.)
     * 
     * @param string $column Column name
     * @return bool
     */
    
    public function setIdColumn($column)
    {
        if (!isset($this->header[$column]))
            throw new Exception('Please declare selected column in header first', 521);
        
        $this -> idColumn = $column;
        return True;
    }
    
    /**
     * Handle action requests
     * 
     * @return mixed
     */
    
    public function dispatchAction()
    {
        if (!isset($_GET['action']))
        {
            return FALSE;
        }
        
        if (!$this->header)
        {
            throw new Exception('Please declare a header first', 522);
        }
        if (in_array($_GET['action'], $this->actions) and $_REQUEST['tableID'] == $this->tableID)
        {
            if (isset($_POST['id']))
            {
                $_POST['id'] = base64_decode($_POST['id']);
            }
            
            return $this -> panthera -> get_filters('uidatasheet.' .$this->tableID. '.' .$_GET['action'], '');
        }
    }
    
    /**
     * Add method as action callback eg. ?action=delete and hooked function that will handle item deletion should be called
     * 
     * @param string $action delete|edit|new
     * @param array $callback Callback like in regular Panthera hooking
     * @return bool
     */

    public function addActionCallback($action, $callback)
    {
        if (in_array($action, $this->actions))
        {
            $this -> panthera -> add_option('uidatasheet.' .$this->tableID. '.' .$action, $callback);
            return TRUE;
        }
    }
    
    /**
     * Add column to header
     * 
     * @param string $columnDataName Name of the key in data array of every record eg. "id" or "name"
     * @param string $columnTitle Column title to show in table
     * @param string $alignment Optional alignment - left, center, right
     * @param bool $bold Optional text decoration
     * @param bool $italics Optional text decoration
     * @param bool $underline Optional text decoration
     * @param bool $sortable Optional is sortable column?
     */
    
    public function headerAddColumn($columnDataName, $columnTitle, $alignment='', $bold=False, $italics=False, $underline=False, $sortable=False, $colspan=0)
    {
        if ($alignment != 'left' and $alignment != 'center' and $alignment != 'right' and $alignment != '')
        {
            $this -> panthera -> logging -> output('Warning: Invalid alignment selected "' .$alignment. '" for column "' .$columnDataName. '"', 'uiDatasheet');
            $alignment = '';
        }
        
        if (!$this->idColumn)
        {
            $this->idColumn = $columnDataName;
        }
        
        $this -> header[$columnDataName] = array(
            'title' => $columnTitle,
            'align' => $alignment,
            'bold' => (bool)$bold,
            'italics' => (bool)$italics,
            'underline' => (bool)$underline,
            'sortable' => (bool)$sortable,
            'colspan' => intval($colspan),
        );
    }
    
    /**
     * Append data to table
     * 
     * @param array $data Data rows to append
     * @param bool $singleRow Is $data a single row?
     * @return bool
     */
    
    public function appendData($data, $singleRow=False)
    {
        if (!$this->header)
        {
            throw new Exception('You must define a header first before appending a data', 532);
        }
        
        if ($singleRow)
        {
            $data = array($data);
        }
        
        // remove columns that isn't defined in header and check if there are all required columns from header list    
        $requiredCount = count($this->header);
        
        foreach ($data as $rowID => $row)
        {
            $i = 0;
            
            foreach ($row as $column => $columnData)
            {
                if (!isset($this->header[$column]))
                {
                    unset($data[$rowID][$column]);
                    continue;
                }
                
                $data[$rowID]['__id'] = base64_encode($data[$rowID][$this->idColumn]);
                $data[$rowID]['__id_html'] = substr(hash('md4', $data[$rowID][$this->idColumn]), 0, 6);
                
                $i++;
            }
            
            if ($requiredCount != $i and !$this->skipColumnsCountCheck)
            {
                throw new Exception('Columns count mismatch, please add required columns to your input data', 334);
            }
        }
        
        $this -> data = $data;
        
        return TRUE;
    }
    
    /**
     * Draw a table from template
     * 
     * @return string
     */
    
    public function draw()
    {
        $data = array(
            'idColumn' => $this -> idColumn,
            'tableID' => $this -> tableID,
            'header' => $this -> header,
            'body' => $this -> data,
            'pagerID' => $this -> pagerID,
            'editButtons' => $this -> editButtons,
            'deleteButtons' => $this -> deleteButtons,
        );
        
        return $this -> panthera -> template -> compile('ui.datasheet.tpl', False, array_merge($this -> panthera -> template -> vars, $data));
    }
    
    /**
     * PHP magic method that represents class as string
     * 
     * @return string 
     */
    
    public function __toString()
    {
        return $this->draw();
    }
}
