<?php
/**
  * Lost password recovery module
  *
  * @package Panthera\modules\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Create a recovery record and send a message with key
  * the message body and topic can be both configured in Panthera configuration keys: "recovery_password_title" and "recovery_password_content"
  *
  * @param string $login
  * @return bool 
  * @author Damian Kęska
  */

function recoveryCreate($login)
{
    global $panthera;

    $user = new pantheraUser('login', $login);

    if (!$user -> exists())
        return False;

    $SQL = $panthera -> db -> query('SELECT `id` FROM `{$db_prefix}password_recovery` WHERE `user_login` = :login', array('login' => $login));

    if ($SQL -> rowCount() > 0)
        return False;

    $newPassword = generateRandomString(16);
    $recoveryKey = generateRandomString(32);
    $values = array('recovery_key' => $recoveryKey, 'login' => $login, 'passwd' => $newPassword);

    // plugins support
    $values = $panthera -> get_filters('recovery_values', $values);

    $SQL = $panthera -> db -> query('INSERT INTO `{$db_prefix}password_recovery` (`id`, `recovery_key`, `user_login`, `date`, `new_passwd`) VALUES (NULL, :recovery_key, :login, NOW(), :passwd)', $values);

    $message = $panthera->config->getKey('recovery_password_content', 'You requested a new password. If you want to change your current password to "{$recovery_passwd}" please visit this url: {$PANTHERA_URL}/pa-login.php?key={$recovery_key}', 'string');
    $message = str_replace('{$recovery_key}', $recoveryKey, str_replace('{$recovery_passwd}', $newPassword, pantheraUrl($message)));
    
    $panthera -> importModule('mailing');
    
    $mailRecovery = new mailMessage();
    $mailRecovery -> setSubject($panthera->config->getKey('recovery_password_title', 'Password recovery', 'string'));
    $mailRecovery -> addRecipient($user->mail);
    $mailRecovery -> send($message, 'html');
    
    //mail($user->mail, $panthera->config->getKey('recovery_password_title', 'Password recovery', 'string'), $message);

    if ($SQL -> rowCount() > 0)
        return True;

    return False;
}

/**
  * Perform activation of new password
  *
  * @param string $key Input key from delivered mail message
  * @return bool 
  * @author Damian Kęska
  */

function recoveryChangePassword($key)
{
    global $panthera;

    $SQL = $panthera -> db -> query('SELECT `user_login`, `new_passwd` FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key', array('key' => $key));

    if ($SQL -> rowCount() > 0)
    {
        $array = $SQL -> fetch();
        $user = new pantheraUser('login', $array['user_login']);

        $user -> changePassword($array['new_passwd']);
        $user -> save();

        // maybe any plugin will use this data
        $panthera -> get_options('recovery_done', array($key, $array['user_login'], $array['new_passwd']));

        // remove recovery option
        $panthera -> db -> query ('DELETE FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key', array('key' => $key));
        
        return True; 
   }
}
