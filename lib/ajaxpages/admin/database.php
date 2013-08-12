<?php
/**
  * Manage database
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'database.tpl';

if (!getUserRightAttribute($user, 'can_manage_databases')) {
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('database');

// PDO driver attributes
$attributes = array( "SERVER_INFO", "SERVER_VERSION", "AUTOCOMMIT", "ERRMODE", "CASE", "CLIENT_VERSION", "CONNECTION_STATUS",
    "ORACLE_NULLS", "PERSISTENT", "PREFETCH",
    "TIMEOUT"
);

$attributesEnglish = array( 'ERRMODE' => 'Error mode', 'CLIENT_VERSION' => 'Client version', 'CONNECTION_STATUS' => 'Connection status', 'TIMEOUT' => 'Connection timeout', 'SERVER_INFO' => 'Server info', 'SERVER_VERSION' => 'Server version');

foreach ($attributes as $attribute) {
    $name = $attribute;

    // user friendly names
    if (array_key_exists($attribute, $attributesEnglish))
        $name = $attributesEnglish[$attribute];

    try {
        $attributesTpl[] = array('name' => localize($name, 'database'), 'value' => $panthera->db->sql->getAttribute(constant("PDO::ATTR_".$attribute)));
    } catch (Exception $e) { /* pass */ }
}

// internal Panthera driver attributes
$pantheraAttributes = array();
$pantheraAttributes[] = array('name' => localize('Socket type', 'database'), 'value' => $panthera->db->getSocketType());

if ($panthera->db->getSocketType() == 'mysql')
{
    $pantheraAttributes[] = array('name' => localize('Server adress', 'database'), 'value' => $panthera->config->getKey('db_host'));
    $pantheraAttributes[] = array('name' => localize('Username', 'database'), 'value' => $panthera->config->getKey('db_username'));
    $pantheraAttributes[] = array('name' => localize('Database name', 'database'), 'value' => $panthera->config->getKey('db_name'));
    $pantheraAttributes[] = array('name' => localize('Prefix', 'database'), 'value' => $panthera->config->getKey('db_prefix'));
	
	if ($panthera->config->getKey('db_timeout') != NULL)
		$pantheraAttributes[] = array('name' => localize('Connection timeout', 'database'), 'value' => $panthera->config->getKey('db_timeout'));
	else
		$pantheraAttributes[] = array('name' => localize('Connection timeout', 'database'), 'value' => '30');
	
	if ($panthera->config->getKey('db_autocommit') != NULL)
		$pantheraAttributes[] = array('name' => localize('Automatic commit mode', 'database'), 'value' => $panthera->config->getKey('db_autocommit'), 'type' => 'bool');
	else
		$pantheraAttributes[] = array('name' => localize('Automatic commit mode', 'database'), 'value' => false, 'type' => 'bool');
	var_dump($panthera->config->getKey('db_autocommit'));
} elseif ($panthera->db->getSocketType() == 'sqlite') {
    $pantheraAttributes[] = array('name' => localize('File', 'database'), 'value' => $panthera->config->getKey('db_file'));
}

$template -> push('sql_attributes', $attributesTpl);
$template -> push('panthera_attributes', $pantheraAttributes);
?>
