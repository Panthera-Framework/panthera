<?php
$config = array('lib' => '../lib',
                'requires_instalation' => true,
                'SITE_DIR' => '{$SITE_DIR}',
                'template' => 'PUT-YOUR-TEMPLATE-NAME-HERE',
                'timezone' => 'Europe/Warsaw',
                'webroot' => '/',
                'db_host' => 'localhost',
                'db_username' => 'panthera',
                'db_name' => 'DATABASE-NAME-HERE',
                'db_password' => 'DATABASE-PASSWORD-HERE',
                'db_prefix' => 'pa_',
                'salt' => 'CUSTOM-PASSWORDS-SALT-HERE',
                'session_key' => 'YOUR-SESSION-KEY',
                'upload_dir' => 'content/uploads',
                'build_missing_tables' => False // create missing SQL tables from template
                );

// executes before plugins
/*function userStartup()
{
    global $panthera;

}

function userDBError($exception)
{
    // eg. send e-mail or text message on phone or jabber if site has connection problems with database
    // mail();
}*/

require $config['lib']. '/boot.php';
?>
