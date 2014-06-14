<?php
/**
 * User management tools
 *
 * @package Panthera\core\user
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * User management tools
 *
 * @package Panthera\core\user
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

class userTools
{
    /**
     * A simple login method
     *
     * @param string $user Login
     * @param string $passwd Password
     * @param bool $forceWithoutPassword Login without password
     * @return bool|string True if success, false on failure and string with error id on error (eg. "BANNED")
     * @package Panthera\core\user
     * @author Damian Kęska
     */

    public static function userCreateSession($user, $passwd, $forceWithoutPassword=False, $onlyCheck=False)
    {
        $panthera = pantheraCore::getInstance();
        $usr = new pantheraUser('login', $user);

        // allow logging in using e-mail address
        if ($panthera -> config -> getKey('login.maillogin', 1, 'bool', 'pa-login'))
        {
            if (!$usr->exists())
                $usr = new pantheraUser('mail', $user);
        }

        // allow logging-in using Jabber address
        if ($panthera -> config -> getKey('login.jabberlogin', 0, 'bool', 'pa-login'))
        {
            if (!$usr->exists())
                $usr = new pantheraUser('jabber', $user);
        }

        if ($usr->exists())
        {
            // force login user without password
            if ($forceWithoutPassword)
            {
                if ($onlyCheck)
                    return $usr;

                $panthera -> user = $usr;
                $panthera -> session -> uid = $usr->id;
                $usr -> lastlogin = DB_TIME_NOW;
                $usr -> lastip = $_SERVER['REMOTE_ADDR'];
                $usr -> save();
                return True;
            }

            if ($usr->isBanned())
                return 'BANNED';

            // check if password is correct
            if ($usr -> checkPassword($passwd)) {

                if ($onlyCheck)
                    return $usr;

                $panthera -> user = $usr;
                $panthera -> session -> uid = $usr->id;
                $usr -> lastlogin = DB_TIME_NOW;
                $usr -> lastip = $_SERVER['REMOTE_ADDR'];
                $usr -> save();
                return $usr;
            }
        }

        return False;
    }

    /**
     * Create user session by user id (without password)
     *
     * @param int $id User id
     * @return bool
     * @package Panthera\core\user
     * @author Damian Kęska
     */

    public static function userCreateSessionById($id)
    {
        $panthera = pantheraCore::getInstance();

        $user = new pantheraUser('id', $id);

        if ($user -> exists())
            static::userCreateSession($user -> login, null, True);

        return False;
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     * @package Panthera\core\user
     * @author Damian Kęska
     */

    public static function userLoggedIn()
    {
        $panthera = pantheraCore::getInstance();

        if (!is_object($panthera->user))
            return False;

        return $panthera -> user -> exists();
    }

    /**
     * Get current logged in user (if logged in)
     *
     * @return pantheraUser
     * @package Panthera\core\user
     * @author Damian Kęska
     */

    public static function getCurrentUser()
    {
        $panthera = pantheraCore::getInstance();

        $sessionKey = $panthera->config->getKey('session_key');

        if($panthera -> session -> get('uid'))
            return new pantheraUser('id', $panthera -> session -> get('uid'));

        return false;
    }

    /**
     * Simply remove user by `name`. Returns True if any row was affected
     *
     * @return bool
     * @package Panthera\core\user
     * @author Mateusz Warzyński
     */

    public static function removeUser($login)
    {
        $u = new pantheraUser('login', $login);

        if ($u -> exists())
            return $u -> delete();

        return False;
    }

    /**
     * Simply logout user
     *
     * @return bool
     * @package Panthera\core\user
     * @author Damian Kęska
     */

    public static function logoutUser()
    {
        $panthera = pantheraCore::getInstance();
        $panthera -> session -> remove ('uid');

        return True;
    }
}