<?php
/**
 * Last logged users dash widget
 *
 * @package Panthera\core\users
 * @license GNU Lesser General Public License 3, see license.txt
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
            if ($value->attributes->superuser)
                continue;

            $users[] = array(
                'login' => $value->getName(),
                'time' => date_calc_diff(strtotime($value->lastlogin), time()),
                'avatar' => pantheraUrl($value->profile_picture),
                'uid' => $value->id,
            );
        }

        $this -> panthera -> template -> push ('lastLogged', $users);
        return $this -> panthera -> template -> compile('dashWidgets/lastLogged.tpl');
    }
}