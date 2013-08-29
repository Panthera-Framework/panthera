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

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_password_recovery'))
{
    $template->display('no_access.tpl');
    pa_exit();
}
 
$panthera -> config -> loadOverlay('passwordrecovery');

$variables = array(
    'recovery_passwd_length' => $panthera -> config -> getKey('recovery.passwd.length'),
    'recovery_key_length' => $panthera -> config -> getKey('recovery.key.length'),
    'recovery_mail_content' => htmlspecialchars(nl2br($panthera -> config -> getKey('recovery.mail.content'))),
    'recovery_mail_title' => $panthera -> config -> getKey('recovery.mail.title')
);

end($variables);

/**
  * Saving all variables back to file
  *
  * @return void
  * @author Damian Kęska
  */

if (isset($_POST[key($variables)]))
{
    foreach ($_POST as $key => $value)
    {
        if (!isset($variables[$key]))
        {
            continue;
        }

        $key = str_replace('_', '.', $key);        
        
        $panthera -> config -> setKey($key, $value);
        $variables[$key] = $value; // update cache
    }
    
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> push ('variables', $variables);
$panthera -> template -> display('settings.passwordrecovery.tpl');
pa_exit();
