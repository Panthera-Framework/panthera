<?php
/**
 * Google PageRank
 * Get GooglePR by given URL and show statistics
 *
 * @package Panthera\core\adminUI\googlepr
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Google PageRank
 * Get GooglePR by given URL and show statistics
 *
 * @package Panthera\core\googlepr\admin
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */
 
class googleprAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Google PageRank', 'googlepr',
    );
    
    protected $permissions = 'admin.googlepr';
	
	/**
	  * Get Google PageRank
	  *
	  * @author Mateusz Warzyński
	  * @return null 
	  */
	
	public function getPageRankAction()
	{
		$domain = $_POST['domain'];
    
	    $results = $this -> panthera -> session -> get('googlepr.history');
	    
	    if (isset($results[$domain]))
	        ajax_exit(array(
	           'status' => 'failed',
	           'message' => localize('Result of your request is on the chart.', 'googlepr'),
            ));
	
	    // check legth of domain
	    if (strlen($domain) < 5)
	        ajax_exit(array(
	           'status' => 'failed',
	           'message' => localize('Given domain is too short', 'googlepr'),
            ));
	    
	    // get PageRank
	    $rank = GooglePR::getRank($domain);
	    
		// limit results to display
	    if (count($results) > 14)
	    {
	        reset($results);
	        $firstKey = key($results);
	        unset($results[$firstKey]);
	    }
	    
	    $results[$domain] = $rank;
	    $this -> panthera -> session -> set ('googlepr.history', $results);

	    ajax_exit(array(
	       'status' => 'success',
        ));
	}
    
	
	
	/**
	  * Display GooglePR site, used simple and beautiful charts
	  *
	  * @author Mateusz Warzyński
	  * @return string 
	  */
	
    public function display()
    {
        $this -> dispatchAction();
		$this -> panthera -> locale -> loadDomain('googlepr');
		$this -> panthera -> template -> push('charResults', array_reverse($this->panthera->session->get('googlepr.history')));
		return $this -> panthera -> template -> compile('googlepr.tpl');
    }
}