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

$tpl = 'conftool.tpl';

if (@$_GET['display'] == 'conftool') {

      if (!getUserRightAttribute($user, 'can_update_config_overlay')) {
            $template->display('no_access.tpl');
            pa_exit();
      }

      $panthera -> locale -> loadDomain('conftool');
      $panthera -> locale -> loadDomain('type');

      if ($_GET['action'] == 'change') {
            /*if (!$panthera -> config -> setKey('paging_users_max', $_POST['paging_users_max'])) {
                   print(json_encode(array('status' => 'failed', 'error' => localize('Error with saving paging_users_max!'))));
                   pa_exit();
            }*/

            $key = $_POST['id'];
            $value = $_POST['value'];

            $type = $panthera->config->getKeyType($key);

            if ($type == 'array')
                $value = unserialize($value);

            $modified = $panthera -> get_filters('conftool_change', array($key, $value));

            if (!is_array($modified))
            {
                ajax_exit(array('status' => 'failed', 'message' => $modified));
            }

            if (!$panthera->config->setKey($modified[0], $modified[1]))
            {
                ajax_exit(array('status' => 'failed', 'message' => localize('Invalid value for this data type')));
                pa_exit();
            }

            //print(json_encode(array('status' => 'failed', 'message' => 'Nie poprawna wartość')));
            //pa_exit();
            print(json_encode(array('status' => 'success')));
            pa_exit();
      }

      $overlay = $panthera -> config -> getOverlay();
      $array = array();

      foreach ($overlay as $key => $value)
      {
          if (is_array($value[1]))
              $value[1] = serialize($value[1]);

          $array[$key] = array($value[0], $value[1]);
      }

      $array = $panthera -> get_filters('conftool_array', $array);

      $template -> push('a', $array);
}

?>
