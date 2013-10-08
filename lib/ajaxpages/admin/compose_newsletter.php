<?php
/**
  * Compose newsletter
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_compose_newsletters')) {
    $noAccess = new uiNoAccess;
    $noAccess -> addMetas(array('can_compose_newsletters'));
    $noAccess -> display();
}

$panthera -> locale -> loadDomain('newsletter');
$panthera -> importModule('newsletter');
$panthera -> template -> setTitle(localize('Compose a new message', 'newsletter'));
$language = $panthera -> locale -> getActive();

$newsletter = new newsletter('nid', $_GET['nid']);

// display error page if newsletter category does not exists
if (!$newsletter->exists())
{
    $noAccess = new uiNoAccess;
    $noAccess -> display();
}

$panthera -> template -> push ('nid', $newsletter->nid);

// recent subscribers
$panthera -> template -> push ('recent_subscribers', $newsletter->getUsers(0, 15));

// we need crontab to get list of jobs
$panthera -> importModule('crontab');

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
            {
                $jobData['data']['done'] = '0';
            }
            
            if (!$jobData['data']['count'])
            {
                $jobData['data']['count'] = '?';
            }
            
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

$panthera -> template -> push ('messages_queue', $jobsTpl);

/**
  * Posting a new message
  *
  * @author Damian Kęska
  */

if(isset($_POST['content']))
{
    if (!getUserRightAttribute($user, 'can_manage_newsletter') and !getUserRightAttribute($user, 'can_manage_newsletter_' .$newsletter->nid)) {
        $noAccess = new uiNoAccess;
        $noAccess -> addMetas(array('can_manage_newsletter', 'can_manage_newsletter_' .$newsletter->nid));
        $noAccess -> display();
    }
    
    // content cannot be shorten than 10 characters
    if (strlen($_POST['content']) < 5)
        ajax_exit(array('status' => 'failed', 'message' => localize('Message is too short', 'newsletter')));

    if (strlen($_POST['title']) < 3 and !$_POST['saveasdraft'])
        ajax_exit(array('status' => 'failed', 'message' => localize('Title is too short', 'newsletter')));
        
    if (@$_POST['putToDrafts'] or @$_POST['saveasdraft'])
    {
        $panthera -> importModule('editordrafts');
        editorDraft::createDraft($_POST['content'], $panthera->user->id);
        
        if (@$_POST['saveasdraft'])
        {
            ajax_exit(array('status' => 'success', 'message' => localize('Saved')));
        }
    }
    
    $options = array(
        'sendToAllUsers' => (bool)$_POST['sendToAllUsers']
    );

    $newsletter -> execute($_POST['content'], htmlspecialchars($_POST['title']), $_POST['from'], $options);

    ajax_exit(array('status' => 'success', 'message' => localize('Sent', 'newsletter')));
}

// titlebar
$titlebar = new uiTitlebar(localize('Newsletter', 'newsletter'). ' - ' .localize('Compose a new message', 'newsletter'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'left');

$attr = unserialize($newsletter -> attributes);

if (!$attr['footer'])
{
    $attr['footer'] = '';
    $newsletter -> attributes = serialize($attr);
    $newsletter -> save();
}

$panthera -> template -> push ('mailFooter', filterInput($attr['footer'], 'wysiwyg'));

/**
  * Footer editing page
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'editFooter')
{
    if (!getUserRightAttribute($user, 'can_manage_newsletter') and !getUserRightAttribute($user, 'can_manage_newsletter_' .$newsletter->nid)) {
        $noAccess = new uiNoAccess;
        $noAccess -> addMetas(array('can_manage_newsletter', 'can_manage_newsletter_' .$newsletter->nid));
        $noAccess -> display();
    }
    
    /**
      * Save newsletter footer
      *
      * @author Damian Kęska
      */

    if (isset($_POST['footerContent']))
    {
        $attr['footer'] = $_POST['footerContent'];
        $newsletter -> attributes = serialize($attr);
        $newsletter -> save();
        ajax_exit(array('status' => 'success'));
    }

    $panthera -> template -> display('newsletter_footer.tpl');
    pa_exit();
}

$panthera -> template -> display('compose_newsletter.tpl');
pa_exit();
