<?php
/**
  * Compose newsletter
  *
  * @package Panthera\core\components\newsletter
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
 * Compose newsletter
 *
 * @package Panthera\core\components\newsletter
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class composeNewsletterAjaxControllerCore extends pageController
{
	protected $permissions = 'can_compose_newsletters';
    
    protected $newsletter = null;
	
	protected $uiTitlebar = array(
		'Compose a new message', 'newsletter'
	);
	
    
    
    /**
     * Create new newsletter message
     * 
     * @author Mateusz Warzyński
     * @return null
     */
     
	protected function postNewMessage()
	{
		$this -> checkPermissions(array('can_manage_newsletter', 'can_manage_newsletter_'.$this->newsletter->nid));
		    
		// content cannot be shorten than 10 characters
		if (strlen($_POST['content']) < 5)
			ajax_exit(array('status' => 'failed', 'message' => localize('Message is too short', 'newsletter')));
		
		if (strlen($_POST['title']) < 3 and !$_POST['saveasdraft'])
		    ajax_exit(array('status' => 'failed', 'message' => localize('Title is too short', 'newsletter')));
		        
		if (@$_POST['putToDrafts'] or @$_POST['saveasdraft'])
		{
		    editorDraft::createDraft($_POST['content'], $this->panthera->user->id);
		        
		    if (@$_POST['saveasdraft'])
				ajax_exit(array('status' => 'success', 'message' => localize('Saved')));
		}
		    
		$options = array(
		    'sendToAllUsers' => (bool)$_POST['sendToAllUsers']
		);
		
		$this -> newsletter -> execute($_POST['content'], htmlspecialchars($_POST['title']), $_POST['from'], $options);
		
		ajax_exit(array('status' => 'success', 'message' => localize('Sent', 'newsletter')));
	}


    
    /**
     * Edit footer
     * 
     * @author Mateusz Warzyński
     * @return null
     */
     
	public function editFooterAction()
	{
		$this -> checkPermissions(array('can_manage_newsletter', 'can_manage_newsletter_' .$this->newsletter->nid));
	    
	    if (isset($_POST['footerContent']))
	    {
	        $attr['footer'] = $_POST['footerContent'];
	        $this -> newsletter -> attributes = serialize($attr);
	        $this -> newsletter -> save();
	        ajax_exit(array('status' => 'success'));
	    }
	
	    $this -> panthera -> template -> display('newsletter_footer.tpl');
	    pa_exit();
	}



    /**
     * Main function, display template
     * 
     * @author Mateusz Warzyński
     * @return string
     */
	
	public function display()
	{
		// get newsletter translates
		$this -> panthera -> locale -> loadDomain('newsletter');
		
		// get active language
		$language = $this -> panthera -> locale -> getActive();
		
		$this -> newsletter = new newsletter('nid', $_GET['nid']);
		
		// display error page if newsletter category does not exists
		if (!$this -> newsletter -> exists()) {
		    $noAccess = new uiNoAccess;
		    $noAccess -> display();
		}
		
		$this -> panthera -> template -> push ('nid', $this->newsletter->nid);
		
		// recent subscribers
		$this -> panthera -> template -> push ('recent_subscribers', $this->newsletter->getUsers(0, 15));
		
		// scheduled jobs
		$jobsTpl = array();
		$jobs = crontab::getJobs('');
		
		foreach ($jobs as $job)
		{
		    if (substr($job->jobname, 0, 10) == "newsletter")
		    {
		        $exp = explode('_', $job->jobname);
		
		        if ($exp[1] == $_GET['nid'])
		        {
		            $jobData = $job->getData();
		            
		            if (!$jobData['data']['done'])
		                $jobData['data']['done'] = '0';
		            
		            if (!$jobData['data']['count'])
		                $jobData['data']['count'] = '?';
		            
		            $jobsTpl[] = array(
		                'title' => $jobData['data']['title'],
		                'created' => $job->created,
		                'count' => $jobData['data']['usersCount'],
		                'offset' => $jobData['data']['offset'],
		                'limit' => $jobData['data']['maxLimit'],
		                'position' => $jobData['data']['done']
		            );
		        }
		    }
		}
		
		$this -> panthera -> template -> push ('messages_queue', $jobsTpl);
		
		if(isset($_POST['content']))
			$this -> postNewMessage();
		
		$attr = unserialize($this -> newsletter -> attributes);
		
		if (!$attr['footer']) {
		    $attr['footer'] = '';
		    $this -> newsletter -> attributes = serialize($attr);
		    $this -> newsletter -> save();
		}
		
		$this -> panthera -> template -> push ('mailFooter', filterInput($attr['footer'], 'wysiwyg'));
		
		$this -> dispatchAction();
		
		return $this -> panthera -> template -> compile('compose_newsletter.tpl');
		
	}
	 
}