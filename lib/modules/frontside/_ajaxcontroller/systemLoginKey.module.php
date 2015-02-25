<?php
/**
 * A simple controller extension that allows logging in to Panthera using a single use "login key"
 * Useful when for example creating a cronjob that must access a page through HTTP call for any reason
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska <damian@pantheraframework.org>
 * @license LGPLv3
 */
$panthera = panthera::getInstance();

if (isset($_GET['_system_loginkey']) and $panthera -> varCache)
{
    if (!$panthera -> varCache -> exists('pa-login.system.loginkey'))
        die('No loginkey present.');

    $loginKey = $panthera -> varCache -> get('pa-login.system.loginkey');

    // login keys are only 128 char length
    if (strlen($_GET['_system_loginkey']) != 128)
        die('Invalid length.');

    if ($_GET['_system_loginkey'] == $loginKey['key'])
    {
        $panthera -> user = new pantheraUser('id', $loginKey['userID']);
        $panthera -> varCache -> remove('pa-login.system.loginkey');
        userTools::userCreateSessionById($loginKey['userID']);
    }
}