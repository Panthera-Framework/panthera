<?php
/**
  * Admin Panel front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

require 'content/app.php';

if (!checkUserPermissions($user))
    pa_redirect('pa-login.php');

$panthera -> template -> setTemplate('admin_mobile');

$tpl = 'no_page.tpl';

$panthera -> get_options('ajax_page');
$display = addslashes($_GET['display']);

if (is_file(PANTHERA_DIR. '/ajaxpages/' .$display. '.php'))
{
    include(PANTHERA_DIR. '/ajaxpages/' .$display. '.php');
} elseif (is_file(SITE_DIR. '/content/ajaxpages/' .$display. '.php')) {
    include(SITE_DIR. '/content/ajaxpages/' .$display. '.php');
}

$template -> display($tpl);
$panthera -> finish();

