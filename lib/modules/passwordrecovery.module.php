<?php
/**
 * Lost password recovery module
 *
 * @package Panthera\core\components\user
 * @author Damian Kęska
 * @license LGPLv3
 */

class pantheraRecovery extends pantheraFetchDB
{
    protected $_tableName = 'password_recovery';
    protected $_constructBy = array(
        'id', 'recovery_key', 'array',
    );
    
    /**
     * Create a recovery record and send a message with key
     * the message body and topic can be both configured in Panthera configuration keys: "recovery_password_title" and "recovery_password_content"
     *
     * @param string $login
     * @return bool
     * @author Damian Kęska
     */
    
    public static function recoveryCreate($login)
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
        
        $recoveryURL = pantheraUrl('{$PANTHERA_URL}/pa-login.php?key=' .$recoveryKey);
        
        try {
            $recoveryURL = panthera::getInstance() -> routing -> generate('login.recovery', array(
                'recoveryKey' => $recoveryKey,
            ));
            
        } catch (Exception $e) {}
    
        $message = str_replace('{$recovery_url}', $recoveryURL, 
                   str_replace('{$recovery_key}', $recoveryKey,
                   str_replace('{$recovery_passwd}', $newPassword,
                   str_replace('{$userName}', $user->getName(),
                   str_replace('{$userID}', $user->id, pantheraUrl($message))))));
    
        $title = str_replace('{$recovery_url}', $recoveryURL, 
                   str_replace('{$recovery_key}', $recoveryKey,
                   str_replace('{$recovery_passwd}', $newPassword,
                   str_replace('{$userName}', $user->getName(),
                   str_replace('{$userID}', $user->id, pantheraUrl($title))))));
    
        if (!$message)
            throw new Exception('No recovery message set for this language', 1);
        
        if (!$title)
            throw new Exception('No recovery message title set for this language', 2);
                   
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
     * Perform activation of new password or account activation
     *
     * @param string $key Input key from delivered mail message
     * @return bool
     * @author Damian Kęska
     */
    
    public static function recoveryChangePassword($key)
    {
        $panthera = pantheraCore::getInstance();
    
        $whereClause = new whereClause;
        $whereClause -> setGroupStatement(2, 'AND');
        $whereClause -> add('AND', 'recovery_key', '=', $key);
        $whereClause -> add('AND', 'type', '=', 'recovery', 2);
        $whereClause -> add('OR', 'type', '=', 'confirmation', 2);
        
        $search = pantheraRecovery::fetchOne($whereClause);
        
        if ($search)
        {
            $user = new pantheraUser('login', $search -> user_login);
    
            if ($search -> new_passwd)
                $user -> changePassword($search -> new_passwd);
    
            $user -> save();
            
            if ($search -> type == 'recovery')
            {
                // maybe any plugin will use this data
                $panthera -> get_options('recovery.done', array($key, $search -> user_login, $search -> new_passwd));
            } elseif ($search -> type == 'confirmation') {
                $panthera -> get_options('confirmation.done', array($key, $search -> user_login));
            }
    
            // remove recovery option
            $search -> delete();
            return True;
       }
    }
}
  
