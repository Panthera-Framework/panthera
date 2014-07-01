<?php
/**
 * Show and manage newsletter users
 *
 * @package Panthera\core\components\newsletter
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */
 
class newsletter_usersAjaxControllerSystem extends pageController
{
    protected $requirements = array(
        'newsletter',
    );
    
    protected $permissions = array('admin.newsletter.management');
    
    /**
     * Additional fields
     * 
     * @var $additionalFields
     */
    
    protected $additionalFields = array();
    
    /**
     * Newsletter object
     * 
     * @var $newsletter
     */
    
    protected $newsletter = null;
    
    /**
     * Remove subscriber from subscribing newsletter
     * 
     * @feature newsletter.users.removesubscriber.before $_POST $newsletterObject Before deletion
     * @feature newsletter.users.removesubscriber.success $_POST $newsletterObject On success
     * @feature newsletter.users.removesubscriber.failure $_POST $newsletterObject On failure
     * @ajax
     * @author Damian Kęska
     * @return null
     */
    
    protected function removeSubscriberAction()
    {
        $this -> getFeature('newsletter.users.removesubscriber.before', $_POST, $this -> newsletter);
        
        if(newsletterManagement::removeSubscriber($_POST['id'])) 
        {
            newsletterManagement::updateUsersCount($_GET['nid']);
            
            $this -> getFeature('newsletter.users.removesubscriber.success', $_POST, $this -> newsletter);
            
            ajax_exit(array(
                'status' => 'success',
            ));
        }
        
        $this -> getFeature('newsletter.users.removesubscriber.failure', $_POST, $this -> newsletter);
        
        ajax_exit(array(
            'status' => 'failed',
            'messsage' => localize('Cannot find subscriber', 'newsletter'),
        ));
    }
    
    /**
     * Add subscriber
     * 
     * @ajax
     * @author Damian Kęska
     * @return null
     */
    
    protected function addSubscriberAction()
    {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Check email address.', 'newsletter'),
            ));
        }
        
        $this -> getFeatureRef('newsletter.users.addsubscriber', $_POST, $this -> newsletter);
        
        $userID = -1; // guest
        $type = '';
        
        if (isset($_POST['user']))
        {
            $u = new pantheraUser('login', $_POST['user']);
        
            if ($u->exists())
                $userID = $u->id;
        }
        
        if (in_array($_POST['type'], $types))
            $type = $_POST['type'];
        
        if ($this -> newsletter -> registerUser($_POST['email'], $type, $userID, '', True, True))
        {
            newsletterManagement::updateUsersCount($_GET['nid']);
            $subscription = $this -> newsletter -> getSubscription($_POST['email']);
            $notes = $subscription['notes'];
        
            if ($_POST['notes'])
            {
                $notes = strip_tags($_POST['notes']);
                $subscriber = new newsletterSubscriber('id', $subscription['id']);
                $metas = $subscriber -> getMetas();
        
                foreach ($this -> additionalFields as $fieldName => $field)
                {
                    if ($_POST['extrafield_' .$fieldName])
                        $metas -> set($fieldName, strip_tags($_POST['extrafield_' .$fieldName]));
                }
        
                $metas -> save();
        
                $this -> getFeatureRef('newsletter.users.addsubscriber.subscriber', $subscriber, $this -> newsletter);
                $subscriber -> notes = $notes;
                $subscriber -> save();
            }
        
            ajax_exit(array(
               'status' => 'success',
               'id' => $subscription['id'],
               'type' => $subscription['type'],
               'address' => $subscription['address'],
               'added' => $subscription['added'],
               'notes' => $notes,
            ));
                
        } else {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Cannot add subscriber', 'newsletter'),
            ));
        }
    }

    /**
     * Main function
     * 
     * @author Damian Kęska
     * @return string
     */
    
    public function display()
    {
        // GET newsletter by `nid` (from GET parameter)
        $this -> newsletter = new newsletter('nid', $_GET['nid']);
        $types = newsletterManagement::getTypes();
        
        // exit if newsletter does not exists (exists method is a built-in method of pantheraFetchDB's abstract class)
        if (!$this -> newsletter -> exists())
            panthera::raiseError('notfound');
        
        // list of all global additiona fields, example: $this -> additionalFields = array('name' => array('Name', 'newsletter'), 'website' => array('Website', 'newsletter'));
        $this -> additionalFields = $this -> getFeature('newsletter.users.additionalFields', array(), $this -> newsletter);
        
        $this -> template -> push(array(
            'nid' => $_GET['nid'],
            'action' => '',
        ));
        
        $this -> dispatchAction();
        
        if ($_GET['action'] == 'show_table')
            $this -> template -> push ('action', 'show_table');
        
        // get count of newsletter users
        $usersCount = $this -> newsletter -> getUsers(False); // false means we dont want to get records but it's count
        $page = intval($_POST['pagenum']);
        
        // pages are only > -1 (we are counting from 0, so the real page is page-1 means page 1 is 0 in code)
        if ($page < 0)
            $page = 0;
        
        // get records only for current page
        $uiPager = new uiPager('adminNewsletter', $usersCount, 'adminNewsletter');
        $uiPager -> setActive($page);
        $uiPager -> setLinkTemplatesFromConfig('newsletter.users.tpl');
        $limit = $uiPager -> getPageLimit();
        
        $this -> getFeatureRef('newsletter.users.pager', $uiPager, $this -> newsletter);
        
        // get all avaliable newsletter types
        $this -> template -> push ('newsletter_types', $types);
        
        // get all users from current page
        $users = $this -> newsletter -> getUsers($limit[0], $limit[1]);
        $usersTpl = array();
        
        foreach ($users as $index => $user)
        {
            $u = new newsletterSubscriber('id', $user['id']);
            $user['metas'] = $u -> getMetas() -> listAll();
            $usersTpl[$index] = $user;
        }
        
        $this -> getFeatureRef('newsletter.users.list', $usersTpl, $this -> newsletter);
        
        $this -> template -> push (array(
            'additionalFields' => $this -> additionalFields,
            'newsletter_users' => $usersTpl,
        ));
        
        return $this -> template -> compile('newsletter.users.tpl');
    }
}
