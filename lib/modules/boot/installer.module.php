<?php
/**
  * Installer stuff at boot time
  *
  * @package Panthera\modules\boot
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

global $panthera;

// if installer file does not exists link it
if (!is_file(SITE_DIR. '/install.php'))
    symlink(PANTHERA_DIR. '/frontpages/install.php', SITE_DIR. '/install.php');

pa_redirect('install.php');