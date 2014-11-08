<?php
/**
 * Last logged users dash widget
 *
 * @package Panthera\core\system\user
 * @license LGPLv3
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

if (!defined('IN_PANTHERA'))
    exit;

/**
 * Gallery dash widget class
 *
 * @package Panthera\core\users
 */

class lastLogged_dashWidget extends pantheraClass
{
    /**
     * Main function that displays widget
     *
     * @return string
     */

    public function display()
    {
        $u = pantheraUser::fetchAll('', 10, 0, 'lastlogin', 'DESC');
        $users = array();

        foreach ($u as $key => $value)
        {
            // skip user when is a root (superuser) or never logged in (invalid lastlogin date)
            if ($value->attributes->superuser || strtotime($value->lastlogin) < 0)
                continue;

            $dateDiff = date_calc_diff(strtotime($value->lastlogin), time());

            $users[] = array(
                'login' => $value->getName(),
                'time' => $dateDiff,
                'avatar' => pantheraUrl($value->profile_picture),
                'uid' => $value->id,
            );
        }

        $this -> panthera -> template -> push ('lastLogged', $users);
        return $this -> panthera -> template -> compile('dashWidgets/lastLogged.tpl');
    }
}