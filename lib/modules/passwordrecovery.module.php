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
    $panthera = pantheraCore::getInstance();

    $user = new pantheraUser('login', $login);

    if (!$user -> exists())
        return False;

    $SQL = $panthera -> db -> query('SELECT `id` FROM `{$db_prefix}password_recovery` WHERE `user_login` = :login AND `type` = "recovery"', array('login' => $login));

    if ($SQL -> rowCount() > 0)
        return False;

    $panthera -> config -> loadSection('paswordrecovery');
    $language = $user -> language;

    // default password length
    if ($panthera->config->getKey('recovery.passwd.length', 16, 'int', 'passwordrecovery') < 3)
        $panthera -> config -> setKey('recovery.passwd.length', 16, 'int', 'passwordrecovery');

    // default recovery key length
    if ($panthera->config->getKey('recovery.key.length', 32, 'int', 'passwordrecovery') < 16)
        $panthera -> config -> setKey('recovery.key.length', 32, 'int', 'passwordrecovery');

    $newPassword = generateRandomString($panthera->config->getKey('recovery.passwd.length'));
    $recoveryKey = generateRandomString($panthera->config->getKey('recovery.key.length'));

    // check if selected key is unique, if not generate a new one until it isnt unique
    $SQL = $panthera -> db -> query('SELECT `id` FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key AND `type` = "recovery"', array('key' => $recoveryKey));

    while ($SQL -> rowCount() > 0)
    {
        $recoveryKey = generateRandomString($panthera->config->getKey('recovery.key.length'));
        $SQL = $panthera -> db -> query('SELECT `id` FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key AND `type` = "recovery"', array('key' => $recoveryKey));
    }

    $values = array(
        'recovery_key' => $recoveryKey,
        'login' => $login,
        'passwd' => $newPassword
    );

    // plugins support
    $values = $panthera -> get_filters('recovery.values', $values);

    $SQL = $panthera -> db -> query('INSERT INTO `{$db_prefix}password_recovery` (`id`, `recovery_key`, `user_login`, `date`, `new_passwd`, `type`) VALUES (NULL, :recovery_key, :login, NOW(), :passwd, "recovery")', $values);

    $messages = $panthera->config->getKey('recovery.mail.content');
    $titles = $panthera->config->getKey('recovery.mail.title');

    if (isset($messages[$language]))
    {
        $message = $messages[$language];
        $title = $titles[$language];
    } elseif (isset($messages['english'])) {
        $message = $messages['english'];
        $title = $titles['english'];
    } else {
        $message = end($messages);
        $title = end($titles);
    }

    $message = str_replace('{$recovery_key}', $recoveryKey,
               str_replace('{$recovery_passwd}', $newPassword,
               str_replace('{$userName}', $user->getName(),
               str_replace('{$userID}', $user->id, pantheraUrl($message)))));

    $title = str_replace('{$recovery_key}', $recoveryKey,
               str_replace('{$recovery_passwd}', $newPassword,
               str_replace('{$userName}', $user->getName(),
               str_replace('{$userID}', $user->id, pantheraUrl($title)))));

    // send a mail
    $panthera -> importModule('mailing');
    $mailRecovery = new mailMessage();
    $mailRecovery -> setSubject($title);
    $mailRecovery -> addRecipient($user->mail);
    $mailRecovery -> send($message, 'html');

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
    $panthera = pantheraCore::getInstance();

    $SQL = $panthera -> db -> query('SELECT `user_login`, `new_passwd` FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key AND `type` = "recovery"', array('key' => $key));

    if ($SQL -> rowCount() > 0)
    {
        $array = $SQL -> fetch();
        $user = new pantheraUser('login', $array['user_login']);

        $user -> changePassword($array['new_passwd']);
        $user -> save();

        // maybe any plugin will use this data
        $panthera -> get_options('recovery.done', array($key, $array['user_login'], $array['new_passwd']));

        // remove recovery option
        $panthera -> db -> query ('DELETE FROM `{$db_prefix}password_recovery` WHERE `recovery_key` = :key', array('key' => $key));

        return True;
   }
}