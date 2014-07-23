<?php
/**
 * Compose newsletter
 *
 * @package Panthera\core\components\newsletter
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Compose newsletter
 *
 * @package Panthera\core\components\newsletter
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class newsletter_composeAjaxControllerCore extends pageController
{
	protected $permissions = 'admin.newsletter.cat.{$nid}';

    protected $newsletter = null;

	protected $uiTitlebar = array(
		'Compose a new message', 'newsletter'
	);
    
    protected $specialCategories = array(
        'users',
    );



    /**
     * Create new newsletter message
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

	protected function postNewMessage()
	{
		$this -> checkPermissions(array('admin.newsletter.management', 'admin.newsletter.cat.'.$_GET['nid']));

		// content cannot be shorten than 10 characters
        if (strlen($_POST['content']) < 5)
            ajax_exit(array(
			    'status' => 'failed',
			    'message' => localize('Message is too short', 'newsletter'),
            ));

		if (strlen($_POST['title']) < 3 and !$_POST['saveasdraft'])
		    ajax_exit(array(
		        'status' => 'failed',
		        'message' => localize('Title is too short', 'newsletter'),
            ));

		if (@$_POST['putToDrafts'] or @$_POST['saveasdraft'])
		{
		    editorDraft::createDraft($_POST['content'], $this->panthera->user->id);

		    if (@$_POST['saveasdraft'])
				ajax_exit(array(
				    'status' => 'success',
                ));
		}

		$options = array(
		    'sendToAllUsers' => (bool)$_POST['sendToAllUsers'],
		    'recipientsData' => isset($_POST['recipientsData']) ? json_decode($_POST['recipientsData'], true) : False,
		);

        try {
    		if ($this -> newsletter -> execute($_POST['content'], htmlspecialchars($_POST['title']), $_POST['from'], $options, '', True))
    		    ajax_exit(array(
    		      'status' => 'success',
                ));
                
        } catch (Exception $e) {
            ajax_exit(array(
                'status' => 'failed',
                'message' => $e -> getMessage(),
            ));
        }
        
 	    ajax_exit(array(
            'status' => 'failed',
            'message' => localize('Unknown error'),
        ));
    }



    /**
     * Edit footer
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

	public function editFooterAction()
	{
		$this -> checkPermissions(array('admin.newsletter.management', 'admin.newsletter.cat.'.$_GET['nid']));

	    if (isset($_POST['footerContent']))
	    {
	        $attr['footer'] = $_POST['footerContent'];
	        $this -> newsletter -> attributes = serialize($attr);
	        $this -> newsletter -> save();
            
	        ajax_exit(array(
	           'status' => 'success',
            ));
	    }

	    $this -> panthera -> template -> display('newsletter.footer.tpl');
	    pa_exit();
	}



    /**
     * Main function, display template
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return string
     */

	public function display()
	{
		// get newsletter translates
		$this -> panthera -> locale -> loadDomain('newsletter');

		// get active language
		$language = $this -> panthera -> locale -> getActive();
        $special = in_array($_GET['nid'], $this -> specialCategories);
        
		$this -> newsletter = new newsletter('nid', $_GET['nid']);

		// display error page if newsletter category does not exists
		if (!$this -> newsletter -> exists() and !$special) 
		{
		    $noAccess = new uiNoAccess;
		    $noAccess -> display();
		}

		$this -> panthera -> template -> push ('nid', $_GET['nid']);

        if (!$special)
        {
    		// recent subscribers
    		$this -> panthera -> template -> push ('recent_subscribers', $this->newsletter->getUsers(0, 15));
		}
        
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

        foreach ($this -> user -> getData() as $key => $value)
        {
            if ($key == 'passwd' or $key == 'group_name' or $key == 'attributes')
                continue;
            
            $tags['{$user.' .$key. '}'] = '';
        }
        
        $tagsTranslations = array(
            '{$user.login}' => localize('Login', 'users'),
        );
        
        $tags['{$config.site_title}'] = localize('Site title', 'settings');
        $tags['{$config.site_description}'] = localize('Site description', 'settings');
        $tags['{$config.site_metas}'] = localize('Meta tags', 'settings');
        $tags['{$PANTHERA_URL}'] = localize('Website main directory url', 'settings');
        
        $tags = array_merge($tags, $tagsTranslations);

		$this -> panthera -> template -> push (array(
		    'messages_queue' => $jobsTpl,
            'specialCategory' => $special,
            'tags' => $tags,
        ));
        
        $this -> getFeatureRef('admin.newsletter.tags.translations', $tags);

		if(isset($_POST['content']))
			$this -> postNewMessage();

        if (!$special)
		  $attr = unserialize($this -> newsletter -> attributes);

		if (!$attr['footer'] and !$special)
		{
		    $attr['footer'] = '';
		    $this -> newsletter -> attributes = serialize($attr);
		    $this -> newsletter -> save();
		}

		$this -> panthera -> template -> push ('mailFooter', filterInput($attr['footer'], 'wysiwyg'));

		$this -> dispatchAction();
		return $this -> panthera -> template -> compile('newsletter.compose.tpl');
	}

}