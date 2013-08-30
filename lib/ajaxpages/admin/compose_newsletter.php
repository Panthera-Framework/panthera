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
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> template -> setTitle(localize('Compose a new message'));
$panthera -> template -> push('site_template_css', '');

$panthera -> importModule('newsletter');

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
            $jobsTpl[] = array('title' => $jobData['data']['title'], 'created' => $job->created);
        }
    }
}

$panthera -> template -> push ('messages_queue', $jobsTpl);

// posting a new message
if(isset($_POST['content']))
{
    // content cannot be shorten than 10 characters
    if (strlen($_POST['content']) < 10)
        ajax_exit(array('status' => 'failed', 'message' => localize('Message is too short')));

    if (strlen($_POST['title']) < 5)
        ajax_exit(array('status' => 'failed', 'message' => localize('Title is too short')));

    $newsletter -> execute($_POST['content'], htmlspecialchars($_POST['title']));

    ajax_exit(array('status' => 'success', 'message' => localize('Sent')));
}

// titlebar
$titlebar = new uiTitlebar(localize('Newsletter', 'newsletter'). ' - ' .localize('Compose a new message', 'newsletter'));
//$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/newsletter-icon.png', 'left');

$panthera -> template -> display('compose_newsletter.tpl');
pa_exit();
