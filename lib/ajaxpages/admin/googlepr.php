<?php
/**
  * Google PageRank
  *
  * @package Panthera\core\ajaxpages
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$panthera -> locale -> loadDomain('googlepr');

$panthera -> importModule('googlepr');

/**
  * Get Google PageRank
  *
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'getPageRank') 
{
    $domain = $_POST['domain'];
    
    $results = $panthera -> session -> get('googlepr.history');
    
    if (array_key_exists($domain, $results))
        ajax_exit(array('status' => 'failed', 'message' => localize('Result of your request is on the chart.', 'googlepr')));

    // check legth of domain
    if (strlen($domain) < 5)
        ajax_exit(array('status' => 'failed', 'message' => localize('Given domain is too short', 'googlepr')));
    
    // get PageRank
    $rank = GooglePR::getRank($domain);
    
    if (!$rank) {
        ajax_exit(array('status' => 'failed', 'message' => localize('Got wrong result, probably your domain is incorrect', 'googlepr')));
    } else {
        
        if (count($results) > 14) {
            reset($results);
            $firstKey = key($results);
            unset($results[$firstKey]);
        }
        
        $results[$domain] = $rank;
        
        $panthera -> session -> set ('googlepr.history', $results);
        
        ajax_exit(array( 'status' => 'success'));
    }
}

$panthera -> template -> push('charResults', array_reverse($panthera -> session -> get ('googlepr.history')));

$titlebar = new uiTitlebar(localize('Google PageRank', 'googlepr'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/google.png', 'left');

$template -> display('googlepr.tpl');

pa_exit();
