<?php
/**
 * Newsletter management
 *
 * @package Panthera\core\components\newsletter
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

  
/**
 * Newsletter management
 *
 * @package Panthera\core\components\newsletter
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */
  
class newsletterAjaxControllerSystem extends pageController
{
	protected $actionPermissions = array(
        'createCategory' => 'admin.newsletter.management',
        'removeCategory' => array('admin.newsletter.management', 'admin.newsletter.cat.{$nid}'),
	);
    
	protected $uiTitlebar = array(
        'Newsletter management', 'newsletter',
    );
    
	
	/**
     * Create new category action
     * 
	 * @author Mateusz Warzyński
	 * @author Damian Kęska
     * @return null
     */
	
	public function createCategoryAction()
	{
		if (strlen($_POST['title']) > 2) 
		{
			if (newsletterManagement::create($_POST['title'], 0, $_POST['type']))
				ajax_exit(array('status' => 'success'));
			else
				ajax_exit(array('status' => 'failed', 'message' => localize('Cannot add newsletter category, check the title.', 'newsletter')));
		} else {
			ajax_exit(array('status' => 'failed', 'message' => localize('Title should contain at least 3 letters.', 'newsletter')));
		}
	}
	
	
	
	/**
	 * Removing a newsletter category
	 *
	 * @author Mateusz Warzyński
	 * @author Damian Kęska
	 * @return null
	 */
	
	public function removeCategoryAction()
	{
		if ($_GET['nid'])
		{
			if (newsletterManagement::remove('nid', $_GET['nid']))
				ajax_exit(array('status' => 'success'));
			else
				ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove a newsletter category.', 'newsletter')));
		} else {
			ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove newsletter category, ID is missing.', 'newsletter')));
		}
	}
	
	
	
	/**
     * Main function, get translates, display template
     * 
	 * @author Mateusz Warzyński
	 * @author Damian Kęska
     * @return string
     */
	
	public function display()
	{
		$this -> panthera -> locale -> loadDomain('newsletter');
        $this -> pushPermissionVariable('nid', $_GET['nid']);
		$this -> dispatchAction();
		
		$page = intval($_GET['page']);
		$count = newsletterManagement::search('', False);
		
		// pager
		$uiPager = new uiPager('adminNewsletterCategories', $count, 'adminNewsletterCategories');
		$uiPager -> setActive($page);
		$uiPager -> setLinkTemplates('#', 'navigateTo(\'?'.getQueryString($_GET, 'page={$page}', '_').'\');');
		$limit = $uiPager -> getPageLimit();
		$newsletters = newsletterManagement::search('', $limit[1], $limit[0]);
		
		if ($_GET['query'] != '') 
		{
		    foreach ($newsletters as $key => $newsletter) 
		    {
		        if ( stripos( $newsletter['title'], $_GET['query'] ) !== False )
		            $news_array[] = $newsletter;
			}
			
		    $newsletters = $news_array;
		}
		
		$this -> panthera -> locale -> loadDomain('search');
		
		$sBar = new uiSearchbar( 'uiTop' );
		$sBar -> setQuery( $_GET['query'] );
		$sBar -> setAddress( '?display=newsletter&cat=admin' );
		$sBar -> navigate( True );
		$sBar -> addIcon( '{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_manage_newsletter', localize( 'Manage permissions' ) );
		
		$this -> panthera -> template -> push('mailingTypes', newsletterManagement::getTypes());
		$this -> panthera -> template -> push('categories', $newsletters);
		
		return $this -> panthera -> template -> compile('newsletter.tpl');
	}	 
}