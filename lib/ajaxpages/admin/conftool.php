<?php
/**
  * Configuration tool to change values in config overlay
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
    
$panthera -> config -> loadOverlay('*');
      
if (@$_GET['display'] == 'conftool') {

      if (!getUserRightAttribute($user, 'can_update_config_overlay')) 
      {
            $noAccess = new uiNoAccess; $noAccess -> display();
      }

      $panthera -> locale -> loadDomain('conftool');
      $panthera -> locale -> loadDomain('type');
      
      /**
        * Editing a key
        *
        * @author Damian Kęska
        */

      if ($_GET['action'] == 'change') 
      {
            /*if (!$panthera -> config -> setKey('paging_users_max', $_POST['paging_users_max'])) {
                   print(json_encode(array('status' => 'failed', 'error' => localize('Error with saving paging_users_max!'))));
                   pa_exit();
            }*/

            $key = $_POST['id'];
            $value = $_POST['value'];
            $section = $_POST['section'];

            $type = $panthera->config->getKeyType($key);

            if ($type == 'array')
                $value = unserialize($value);

            $modified = $panthera -> get_filters('conftool_change', array($key, $value));

            if (!is_array($modified))
            {
                ajax_exit(array('status' => 'failed', 'message' => $modified));
            }

            if (!$panthera->config->setKey($modified[0], $modified[1], $type, $section))
            {
                ajax_exit(array('status' => 'failed', 'message' => localize('Invalid value for this data type')));
                pa_exit();
            }

            ajax_exit(array('status' => 'success'));

      /**
        * Removing existing key
        *
        * @author Damian Kęska
        */

      } elseif ($_GET['action'] == 'remove') {
        $key = $_POST['key'];
        
        if ($panthera -> config -> removeKey($key))
            ajax_exit(array('status' => 'success'));
            
        // the key propably does not exists
        ajax_exit(array('status' => 'failed', 'message' => localize('The key propably does not exist', 'conftool')));
      } elseif ($_GET['action'] == 'add') {
      	if ($_POST['key'] != '' and $_POST['value'] != '' and $_POST['type'] != '') {
			if ($panthera -> config -> setKey($_POST['key'], $_POST['value'], $_POST['type'], $_POST['section']))
				ajax_exit(array('status' => 'success'));
		}
		
      	ajax_exit(array('status' => 'failed', 'message' => 'Check your type of value!'));
	  }
	  
	  $panthera -> importModule('admin/ui.searchbar');
	  $panthera -> locale -> loadDomain('search');
	  
	  $sBar = new uiSearchbar('uiTop');
      
      //$sBar -> setMethod('POST');
      $sBar -> setQuery($_GET['query']);
      $sBar -> setAddress('?display=conftool&cat=admin');
      $sBar -> navigate(True);
      $sBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_update_config_overlay', localize('Manage permissions'));
      $sBar -> addSetting('order', localize('Order by', 'search'), 'select', array(
            'key' => array('title' => localize('Key'), 'selected' => ($_GET['order'] == 'key')),
            'section' => array('title' => localize('Section', 'conftool'), 'selected' => ($_GET['order'] == 'section')),
        ));

      $overlay = $panthera -> config -> getOverlay();
      $array = array();

      foreach ($overlay as $key => $value)
      {
          $value[3] = '';
		  $add = True;
	  	  
		  // check if query is defined
	  	  if ($_GET['query']) {
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
	          
	          $array[$key] = array($value[0], $value[1], 'b64' => $value[3], 'section' => $value[2]);
          }
      }

      $array = $panthera -> get_filters('conftool_array', $array);

      $template -> push('a', $array);
}

$panthera -> template -> display('conftool.tpl');
pa_exit();
