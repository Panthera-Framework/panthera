<?php
/**
 * Configuration tool to change values in config overlay
 *
 * @package Panthera\core\ajaxpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */
 
 
/**
 * Configuration tool to change values in config overlay
 *
 * @package Panthera\core\ajaxpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

 class conftoolAjaxControllerCore extends pageController
{
    protected $permissions = array('admin.conftool' => array('Advanced system configuration editor', 'conftool'));
    
    protected $uiTitlebar = array(
        'Advanced system configuration editor', 'conftool'
    );
    
    /**
     * Removing existing key
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
    
    public function removeAction()
    {
        $key = str_replace('_-_', '.', $_POST['key']);
    
        $this -> panthera -> logging -> output('Input key='.$key, 'conftool');
        
        if ($this -> panthera -> config -> removeKey($key))
            ajax_exit(array('status' => 'success'));
        
        // the key propably does not exists
        ajax_exit(array('status' => 'failed', 'message' => localize('The key propably does not exist', 'conftool')));
    }
    
    
    
    /**
     * Editing a key
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
        
    
    public function changeAction()
    {
        $key = str_replace('_-_', '.', $_POST['id']);
        $value = $_POST['value'];
        $section = $_POST['section'];
        
        $type = $this->panthera->config->getKeyType($key);
        
        
        if ($type == 'array')
            $value = unserialize( $value );
        
        $modified = $this->panthera->get_filters('conftool_change' , array($key, $value));
        
        if (!is_array( $modified ) ) {
            ajax_exit(array('status' => 'failed', 'message' => $modified));
        }
        
        if (!$this -> panthera -> config -> setKey($modified[0], $modified[1], $type, $section)) {
            ajax_exit(array('status' => 'failed', 'message' => localize( 'Invalid value for this data type')));
            pa_exit();
        }
        
        ajax_exit(array('status' => 'success'));
    }
    
    
    
    /**
     * Add key to config
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
     
    public function addAction()
    {
        if ($_POST['key'] != '' and $_POST['value'] != '' and $_POST['type'] != '')
        {
            if ($this -> panthera -> config -> setKey($_POST['key'], $_POST['value'], $_POST['type'], $_POST['section']))
                ajax_exit(array('status' => 'success'));
        }
        
        ajax_exit(array('status' => 'failed', 'message' => 'Check your type of value!'));
    }
    
    
    
    /**
     * Main, display function
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */
     
    public function display()
    {
        // load configuration variables from all overlays
        $this -> panthera -> config -> loadOverlay('*');
        
        // load needed translates
        $this -> panthera -> locale -> loadDomain('conftool');
        $this -> panthera -> locale -> loadDomain('type');
        
        $this -> dispatchAction();
        
        $this -> panthera -> locale -> loadDomain( 'search' );

        $sBar = new uiSearchbar( 'uiTop' );
        
        $sBar -> setQuery( $_GET['query'] );
        $sBar -> setAddress( '?display=conftool&cat=admin' );
        $sBar -> navigate( True );
        $sBar -> addIcon( '{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_update_config_overlay', localize( 'Manage permissions' ) );
        $sBar -> addSetting('order', localize('Order by', 'search'), 'select', array(
            'key' => array('title' => localize('Key'), 'selected' => ($_GET['order'] == 'key')),
            'section' => array('title' => localize('Section', 'conftool'), 'selected' => ($_GET['order'] == 'section')) 
        ));
        
        $overlay = $this->panthera->config->getOverlay();
        $array = array();
        
        foreach ($overlay as $key => $value)
        {
            $value[3] = '';
            $add = True;
            
            // check if query is defined
            if ($_GET['query'])
            {
                $add = False;
                
                if ($_GET['order'] == 'key')
                    $find = $key;
                if ($_GET['order'] == 'section')
                    $find = $value[2];
                
                if (stripos($find, $_GET['query']) !== False)
                    $add = True;
            }
            
            if ($add == True) {
                    
                if (is_array($value[1])) {
                    $value[1] = serialize($value[1]);
                    $value[3] = base64_encode($value[1]);
                }
                
                $array[$key] = array( $value[0], $value[1], 'b64' => $value[3], 'section' => $value[2]);
            }
        }
        
        $array = $this->panthera->get_filters('conftool_array', $array);
        $this -> panthera -> template -> push('a', $array);
        return $this -> panthera -> template -> compile('conftool.tpl');
    }

}