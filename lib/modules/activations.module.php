<?php
/**
 * Key activation module
 * Provides a feature to generate a random, unique string that will be sent to mail or jabber to confirm action
 *
 * @package Panthera\core\components\user
 * @author Damian Kęska
 * @license LGPLv3
 */

class activation extends pantheraFetchDB
{
    protected $_tableName = 'password_recovery';
    protected $_constructBy = array(
        'id', 'recovery_key', 'array',
    );

    /**
     * Get user activation of selected type
     *
     * @param $login Login
     * @param string $type Type of activation (eg. recovery, newAccount)
     * @return bool|object
     */

    public static function getActivation($login, $type='recovery')
    {
        $where = new whereClause;
        $where -> add('AND', 'user_login', '=', $login);
        $where -> add('AND', 'type', '=', $type);

        return self::fetchOne($where);
    }
    
    /**
     * Create an activation record and send a message with key
     *
     * @param string $login
     * @param string $type Defaults to recovery, but from here can be also generated other keys that should be activated from mail/jabber
     * @param bool|null $sendMail Send a mail?
     * @param bool $skipUserCheck Skip checking for user? (used in registration)
     * @return bool
     * @author Damian Kęska
     */
    
    public static function newActivation($login, $type='recovery', $sendMail=true, $skipUserCheck=False)
    {
        $panthera = pantheraCore::getInstance();
        $panthera -> config -> loadSection('activation');

        if (!$skipUserCheck)
        {
            $user = new pantheraUser('login', $login);

            if (!$user->exists())
                return False;
        }
        
        $whereClause = new whereClause;
        $whereClause -> add('AND', 'user_login', '=', $login);
        $whereClause -> add('AND', 'type', '=', $type);
        
        $search = self::fetchOne($whereClause);
    
        if ($search)
        {
            $panthera -> logging -> output('Activation code of type "' .$type. '" already created for user "' .$login. '"', 'activation');
            throw new Exception('Activation code of type "' .$type. '" already created for user "' .$login. '"', 331);
        }

        /**
         * Generate random password of fixed length
         * 
         */
    
        // default password length
        if ($panthera-> config ->getKey('activation.passwd.length', 16, 'int', 'activation') < 3)
            $panthera -> config -> setKey('activation.passwd.length', 16, 'int', 'activation');
    
        // default recovery key length
        if ($panthera-> config ->getKey('activation.key.length', 32, 'int', 'activation') < 16)
            $panthera -> config -> setKey('activation.key.length', 32, 'int', 'activation');

        $newPassword = '';
        
        // generate password only when recovering password
        if ($type == 'recovery')
            $newPassword = generateRandomString($panthera->config->getKey('activation.passwd.length'));
        
        
        /**
         * Generate unique recovery key
         */
        
        $key = generateRandomString($panthera->config->getKey('activation.key.length'));
        
        // check if selected key is unique, if not generate a new one until it isnt unique
        $search = self::fetchOne(array(
            'recovery_key' => $key,
        ), false);

        while ($search)
        {
            $key = generateRandomString($panthera->config->getKey('activation.key.length'));
            
            $search = self::fetchOne(array(
                'recovery_key' => $key,
            ), false);
        }
        
        $values = array(
            'recovery_key' => $key,
            'user_login' => $login,
            'new_passwd' => $newPassword,
            'type' => $type,
        );
    
        // plugins support
        $values = $panthera -> executeFilters('activation.values', $values);
        $createResult = self::create($values);

        if ($sendMail === false)
            return $key;
        
        /**
         * Send mail
         */

        $recoveryURL = pantheraUrl('{$PANTHERA_URL}/pa-login.php?key=' .$key);
        
        try {
            $recoveryURL = panthera::getInstance() -> routing -> generate('login.recovery', array(
                'recoveryKey' => $key,
            ));

        } catch (Exception $e) {}
        
        $variables = array(
            'link' => $recoveryURL,
            'recovery_url' => $recoveryURL,
            'key' => $key,
            'passwd' => $newPassword,
            'userName' => $user->getName(),
            'userID' => $user->id,
        );
        
        mailMessage::sendMail($type, true, null, $user->mail, $variables, null, null, $user->language);
        return True;
    }
    
    /**
     * Perform activation of new password or account activation
     *
     * @param string $key Input key from delivered mail message
     * @return bool
     * @author Damian Kęska
     */
    
    public static function activateNewPassword($key)
    {
        $panthera = pantheraCore::getInstance();
    
        $whereClause = new whereClause;
        $whereClause -> setGroupStatement(2, 'AND');
        $whereClause -> add('AND', 'recovery_key', '=', $key);
        $whereClause -> add('AND', 'type', '=', 'recovery', 2);
        $whereClause -> add('OR', 'type', '=', 'confirmation', 2);
        
        $search = self::fetchOne($whereClause);
        
        if ($search)
        {
            $user = new pantheraUser('login', $search -> user_login);
    
            if ($search -> new_passwd)
                $user -> changePassword($search -> new_passwd);
    
            $user -> save();
            
            if ($search -> type == 'recovery')
            {
                // maybe any plugin will use this data
                $panthera -> execute('recovery.done', array($key, $search -> user_login, $search -> new_passwd));

            } elseif ($search -> type == 'confirmation')
                $panthera -> execute('confirmation.done', array($key, $search -> user_login));

            // remove recovery option
            $search -> delete();
            return True;
       }
    }
}
