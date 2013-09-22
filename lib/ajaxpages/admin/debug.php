<?php
/**
  * Debug tools and debug.log
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @hook ajaxpages.debug.tools
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;


if (!getUserRightAttribute($user, 'can_see_debug')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('debug');
$panthera -> locale -> loadDomain('dash');
$panthera -> locale -> loadDomain('ajaxpages');

$toFile = $panthera -> logging -> tofile;
$panthera -> logging -> tofile = False;

    /** JSON PAGES **/

/**
 * Change debug value
 *
 * @author Damian Kęska
 */
 
if ($_GET['action'] == 'toggle_debug_value') {
      if (!getUserRightAttribute($user, 'can_manage_debug')) {
          $noAccess = new uiNoAccess; $noAccess -> display();
          pa_exit();
      }

      if ($panthera -> config -> setKey('debug', !(bool)$panthera -> config -> getKey('debug', False, 'bool'), 'bool'))
            ajax_exit(array('status' => 'success'));

      ajax_exit(array('status' => 'failed'));
      
/**
  * Set messages filtering mode
  *
  * @author Damian Kęska
  */
      
} elseif ($_GET['action'] == 'setMessagesFilter') {
    
    switch ($_POST['value'])
    {
        case 'whitelist':
            $panthera->session->set('debug.filter.mode', 'whitelist');
        break;
        
        case 'blacklist':
            $panthera->session->set('debug.filter.mode', 'blacklist');
        break;
        
        default:
            $panthera->session->remove('debug.filter.mode');
        break;
    }
    
    ajax_exit(array('status' => 'success'));
    
/**
  * Add or remove filter
  *
  * @author Damian Kęska
  */
  
} elseif ($_GET['action'] == 'manageFilterList') {

    $filters = $panthera -> session -> get('debug.filter');
    $filterName = $_POST['filter'];
    
    if ($filterName == '' or !ctype_alpha($filterName))
        ajax_exit(array('status' => 'failed'));
    
    if (!is_array($filters))
        $filters = array();

    if (!array_key_exists($filterName, $filters))
        $filters[$filterName] = True;
    else
        unset($filters[$filterName]);
        
    // save filter list
    $panthera -> session -> set('debug.filter', $filters);    
    
    $filtersTpl = array();
    foreach ($filters as $filter => $enabled)
        $filtersTpl[] = $filter;

    ajax_exit(array('status' => 'success', 'filter' => implode(', ', $filtersTpl)));
}

    /** END OF JSON PAGES **/


    /** Ajax-HTML PAGES **/

// list of links (editable via @hook ajaxpages.debug.tools)
$tools = array();
$tools[] = array('link' => '?display=settings&cat=admin&action=system_info', 'name' => localize('Informations about system and session'));
$tools[] = array('link' => '?display=debhook&cat=admin', 'name' => localize('Plugins debugger'));
$tools[] = array('link' => '?display=includes&cat=admin', 'name' => localize('List of all included files in current code execution'));
$tools[] = array('link' => '?display=errorpages&cat=admin', 'name' => localize('Test system error pages in one place'));
$tools[] = array('link' => '?display=syschecksum&cat=admin', 'name' => localize('Checksum of system files'));
$tools[] = array('link' => '?display=shellutils&cat=admin', 'name' => localize('Shell utils'));
$tools[] = array('link' => '?display=phpinfo&cat=admin', 'name' => localize('phpinfo'));
$tools[] = array('link' => '?display=database&cat=admin', 'name' => localize('Database management'));
$tools[] = array('link' => '?display=dumpinput&cat=admin', 'name' => localize('DumpInput'));
$tools[] = array('link' => '?display=mergephps&cat=admin', 'name' => ucfirst(localize('merge phps and json arrays', 'dash')));
$tools[] = array('link' => '?display=ajaxpages&cat=admin', 'name' => localize('Complete list of all ajax avaliable subpages', 'ajaxpages'));
$tools[] = array('link' => '?display=_popup_jsonedit&cat=admin', 'name' => localize('Array editor', 'debug'));
$tools[] = array('link' => '?display=autoloader&cat=admin', 'name' => localize('Autoloader cache', 'debug'));
$tools[] = array('link' => '?display=generate_password&cat=admin', 'name' => localize('Generate password', 'debug'));
$tools = $panthera -> get_filters('ajaxpages.debug.tools', $tools);

// Displaying main debug site
if (is_file(SITE_DIR. '/content/tmp/debug.log'))
{
      $log = explode("\n", $panthera -> logging -> readSavedLog());
      $template -> push('debug_log', $log);
}

// message filter type
if ($panthera -> session -> get('debug.filter.mode'))
    $panthera -> template -> push('messageFilterType', $panthera -> session -> get('debug.filter.mode'));
else
    $panthera -> template -> push('messageFilterType', '');

// example filters
$exampleFilters = array('pantheraCore', 'pantheraUser', 'pantheraGroup', 'pantheraTemplate', 'pantheraLogging', 'pantheraLocale', 'pantheraFetchDB', 'pantheraDB', 'leopard', 'metaAttributes', 'scm');

foreach ($panthera->logging->getOutput(True) as $line) {
    if(!in_array($line[1], $exampleFilters))
        $exampleFilters[] = $line[1];
}

$panthera -> template -> push ('exampleFilters', $exampleFilters);

// list of all defined filters
$filtersTpl = array();
foreach ($panthera -> session -> get('debug.filter') as $filter => $enabled)
    $filtersTpl[] = $filter;
    
// debug.log save handlers
$logHandlers = array();

if ($panthera -> logging -> toVarCache)
    $logHandlers[] = 'varCache';
    
if ($toFile)
    $logHandlers[] = 'file';

$panthera -> template -> push ('filterList', implode(', ', $filtersTpl));
$panthera -> template -> push ('logHandlers', implode(', ', $logHandlers));
$panthera -> template -> push ('current_log', explode("\n", $panthera -> logging -> getOutput()));
$panthera -> template -> push ('debug', $panthera -> config -> getKey('debug'));
$panthera -> template -> push ('tools', $tools);

$titlebar = new uiTitlebar(localize('Debugging center'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/developement.png', 'left');

$panthera -> template -> display('debug.tpl');
pa_exit();
