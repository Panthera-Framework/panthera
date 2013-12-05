<?php
/**
  * Scheduled jobs management - crontab
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_manage_cronjobs')) {
    $noAccess = new uiNoAccess; 
    $noAccess -> display();
}

/**
  * Create a new cron job
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'postANewJob')
{
    $panthera -> importModule('crontab');
    
    if (!$_POST['jobname'])
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Please enter a job name', 'crontab')));
    }

    // check if job with selected jobname already exists    
    $test = new crontab('jobname', $_POST['jobname']);
    if ($test -> exists())
    {
        ajax_exit(array('status' => 'failed', 'message' => slocalize('Job name "%s" already exists', 'crontab', strip_tags($_POST['jobname']))));
    }
    
    try {
        $cron = Cron\CronExpression::factory($_POST['time_minute']. ' ' .$_POST['time_hour']. ' ' .$_POST['time_day']. ' ' .$_POST['time_month']. ' ' .$_POST['time_weekday']. ' ' .$_POST['time_year']);
        $time = $cron -> getNextRunDate();
        $time = $time->getTimeStamp();
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', 'message' => slocalize('Invalid crontab syntax, details: %s', 'crontab', $e -> getMessage())));
    }
    
    // we are not checking these data now because crontab::createJob will do this thing
    if ($_POST['class'])
    {
        $function = array($_POST['class'], $_POST['function']);
    } else {
        $function = $_POST['function'];
    }
    
    try {
        crontab::createJob($_POST['jobname'], $function, $_POST['jobdata'], $_POST['time_minute'], $_POST['time_hour'], $_POST['time_day'], $_POST['time_month'], $_POST['time_weekday'], $_POST['time_year']);
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', 'message' => slocalize('Cannot create crontab, details: %s', 'crontab', $e -> getMessage())));
    }
    
    ajax_exit(array('status' => 'success', 'time' => date('G:i:s d.m.Y', $time), 'timestamp' => $time));
}

/**
  * List of all cronjobs
  *
  * @param string name
  * @return mixed 
  * @author Damian Kęska
  */
  
$sBar = new uiSearchbar('uiTop');
//$sBar -> setMethod('POST');
$sBar -> setQuery($_GET['query']);
$sBar -> setAddress('?display=crontab&cat=admin');
$sBar -> navigate(True);
$sBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_manage_cronjobs', localize('Manage permissions'));

$filters = array();

// search query
if (@$_GET['query'])
{
    $filters['jobname*LIKE*'] = '%' .trim(strtolower($_GET['query'])). '%';
}

// total count of jobs
$jobsCount = crontab::getJobs($filters, False, False);

$uiPager = new uiPager('adminCronjobs', $jobsCount, 'adminCronjobs', 16);
$uiPager -> setActive(intval($_GET['page']));
$uiPager -> setLinkTemplatesFromConfig('crontab.tpl');
$limit = $uiPager -> getPageLimit();

// search for jobs
$jobsTmp = crontab::getJobs($filters, $limit[1], $limit[0]);
$jobs = array();

foreach ($jobsTmp as $job)
{
    $left = $job->count_left;

    if ($left == -1)
    {
        $left = localize('infinitely', 'crontab');
    }
    
    $jobs[] = array(
        'id' => $job->jobid,
        'name' => $job->jobname,
        'next_iteration' => date('G:i:s d.m.Y', $job->next_interation),
        'crontab_string' => $job->minute. ' ' .$job->hour. ' ' .$job->day. ' ' .$job->month. ' ' .$job->year. ' ' .$job->weekday,
        'count_left' => $left,
        'count_executed' => $job->count_executed,
        'created' => date('G:i:s d.m.Y', strtotime($job->created))
    );
}

$panthera -> template -> push('cronjobs', $jobs);
$panthera -> template -> display('crontab.tpl');
pa_exit();
