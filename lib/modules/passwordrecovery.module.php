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
     * @param bool|null $sendMail Send recovery mail
     * @return bool
     * @author Damian Kęska
     */
    
    public static function recoveryCreate($login, $type='recovery', $sendMail=true)
    {
        $panthera = pantheraCore::getInstance();
        $panthera -> config -> loadSection('paswordrecovery');
    
        $user = new pantheraUser('login', $login);
    
        if (!$user -> exists())
            return False;
        
        $language = $user -> language;
        $whereClause = new whereClause;
        $whereClause -> add('AND', 'user_login', '=', $login);
        $whereClause -> add('AND', 'type', '=', $type);
        
        $search = pantheraRecovery::fetchOne($whereClause);
    
        if ($search)
        {
            $panthera -> logging -> output('Activation code of type "' .$type. '" already created for user "' .$login. '"', 'pantheraRecovery');
            return false;
        }

        /**
         * Generate random password of fixed length
         * 
         */        
    
        // default password length
        if ($panthera->config->getKey('recovery.passwd.length', 16, 'int', 'passwordrecovery') < 3)
            $panthera -> config -> setKey('recovery.passwd.length', 16, 'int', 'passwordrecovery');
    
        // default recovery key length
        if ($panthera->config->getKey('recovery.key.length', 32, 'int', 'passwordrecovery') < 16)
            $panthera -> config -> setKey('recovery.key.length', 32, 'int', 'passwordrecovery');
        
        $newPassword = '';
        
        // generate password only when recovering password
        if ($type == 'recovery')
            $newPassword = generateRandomString($panthera->config->getKey('recovery.passwd.length'));
        
        
        /**
         * Generate unique recovery key
         * 
         */
        
        $recoveryKey = generateRandomString($panthera->config->getKey('recovery.key.length'));
        
        // check if selected key is unique, if not generate a new one until it isnt unique
        $search = pantheraRecovery::fetchOne(array(
            'recovery_key' => $recoveryKey,
        ), false);

        while ($search)
        {
            $recoveryKey = generateRandomString($panthera->config->getKey('recovery.key.length'));
            
            $search = pantheraRecovery::fetchOne(array(
                'recovery_key' => $recoveryKey,
            ), false);
        }
        
        $values = array(
            'recovery_key' => $recoveryKey,
            'user_login' => $login,
            'new_passwd' => $newPassword,
            'type' => $type,
        );
    
        // plugins support
        $values = $panthera -> get_filters('recovery.values', $values);
        $createResult = pantheraRecovery::create($values);

        if ($sendMail === false)
            return $recoveryKey;
        
        /**
         * Send mail
         * 
         */
         
        $recoveryURL = pantheraUrl('{$PANTHERA_URL}/pa-login.php?key=' .$recoveryKey);
        
        try {
            $recoveryURL = panthera::getInstance() -> routing -> generate('login.recovery', array(
                'recoveryKey' => $recoveryKey,
            ));

        } catch (Exception $e) {}
        
        $variables = array(
            'link' => $recoveryURL,
            'recovery_url' => $recoveryURL,
            'recovery_key' => $recoveryKey,
            'recovery_passwd' => $newPassword,
            'userName' => $user->getName(),
            'userID' => $user->id,
        );
        
        mailMessage::sendMail('passwordRecovery', true, null, $user->mail, $variables, null, null, $user->language);
        return True;
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
  
