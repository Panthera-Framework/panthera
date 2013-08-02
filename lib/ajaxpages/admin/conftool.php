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
      
if (@$_GET['display'] == 'conftool') {

      if (!getUserRightAttribute($user, 'can_update_config_overlay')) 
      {
            $template->display('no_access.tpl');
            pa_exit();
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
        ajax_exit(array('status' => 'failed', 'message' => localize('The key propably does not exists', 'conftool')));
      }

      $panthera -> config -> loadOverlay('*');
      $overlay = $panthera -> config -> getOverlay();
      $array = array();

      foreach ($overlay as $key => $value)
      {
          $value[3] = '';
      
          if (is_array($value[1]))
          {
              $value[1] = serialize($value[1]);
              $value[3] = base64_encode($value[1]);
          }
          
          $array[$key] = array($value[0], $value[1], 'b64' => $value[3], 'section' => $value[2]);
      }

      $array = $panthera -> get_filters('conftool_array', $array);

      $template -> push('a', $array);
}

$panthera -> template -> display('conftool.tpl');
pa_exit();