<?php
/**
 * Facebook integration for pa-login
 *
 * @package Panthera\modules\login\facebook
 * @author Damian Kęska
 * @license GNU Affero General Public License 3, see license.txt
 */

$panthera = pantheraCore::getInstance();

$panthera -> template -> push('facebook', True);

if (isset($_GET['facebook']))
{
    if ($_GET['facebook'] != 'ready')
    {
        pa_redirect('/?display=facebook.connect&back=' .$panthera->config->getKey('login.seourl', 'pa-login.php', 'string', 'pa-login'). '?facebook=ready');
    } else {
        $panthera -> importModule('facebook');
        $facebookDetails = null;

        try {
            $facebook = new facebookWrapper;
            $facebookDetails = $facebook->api('/me');
        } catch (Exception $e) {
            $panthera -> template -> push('message', localize('Cannot authenticate with Facebook, please ensure all permissions are accepted and correct', 'facebook'));
        }

        if ($facebookDetails)
        {
            $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}metas` WHERE `name` = "facebook" AND `type` = "u" AND `value` = :value', array('value' => serialize(intval($facebookDetails['id']))));

            if ($SQL -> rowCount())
            {
                $fetch = $SQL -> fetch(PDO::FETCH_ASSOC);

                $u = new pantheraUser('id', $fetch['userid']);

                $session = userTools::userCreateSession($u->login, '', True);

                if (!$session)
                {
                    $panthera -> template -> push('message', localize('Cannot login with Facebook, unknown error when creating session', 'facebook'));
                } else {
                    pa_redirect($panthera->config->getKey('login.seourl', 'pa-login.php', 'string', 'pa-login'));
                }
            } else {
                $panthera -> template -> push('message', localize('There is no any account linked up to your Facebook account. You can register a new one.', 'facebook'));
            }
        }
    }
}