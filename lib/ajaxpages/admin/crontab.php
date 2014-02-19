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
    
    ajax_exit(array('status' => 'success', 'time' => date($panthera -> dateFormat, $time), 'timestamp' => $time));
}



/**
  * Get list of class methods (useful for ajax autocompletion
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'getClassFunctions')
{
    $className = $_POST['className'];
    
    if (!class_exists($className))
    {
        $autoloader = $panthera -> config -> getKey('autoloader');
        
        if (isset($autoloader[$className]))
        {
            $panthera -> importModule($autoloader[$className]);
        }
    }
    
    // if it still does not exists
    if (!class_exists($className))
    {
        ajax_exit(array('status' => 'failed'));
    }
    
    $methods = @get_class_methods($className);
    ajax_exit(array('status' => 'success', 'result' => $methods));
}

if ($_GET['action'] == 'saveJobDetails')
{
    $job = new crontab('jobid', intval($_GET['jobid']));
    $function = null;
    
    if (!$job->exists())
    {
        $noAccess = new uiNoAccess; 
        $noAccess -> display();
    }
    
    try {
        $cron = Cron\CronExpression::factory($_POST['minute']. ' ' .$_POST['hour']. ' ' .$_POST['day']. ' ' .$_POST['month']. ' ' .$_POST['weekday']. ' ' .$_POST['year']);
        $time = $cron -> getNextRunDate();
        $time = $time->getTimeStamp();
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', 'message' => slocalize('Invalid crontab syntax, details: %s', 'crontab', $e -> getMessage())));
    }
    
    if (isset($_POST['class']))
    {
        if (!class_exists($_POST['class']))
        {
            $autoloader = $panthera -> config -> getKey('autoloader');
        
            if (isset($autoloader[$_POST['class']]))
            {
                $panthera -> importModule($autoloader[$_POST['class']]);
            }
        }
        
        if (!class_exists($_POST['class']))
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('Invalid class name specified', 'crontab')));
        }
        
        if (!method_exists($_POST['class'], $_POST['function']))
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('Invalid class method name specified', 'crontab')));
        }
    
        $function = array($_POST['class'], $_POST['function']);
    } else {
    
        if (isset($_POST['function']))
        {
            if (!function_exists($_POST['function']))
            {
                ajax_exit(array('status' => 'failed', 'message' => localize('Invalid function name specified', 'crontab')));
            }
        
            $function = $_POST['function'];
        }
    }
    
    $countLeft = $_POST['count_left'];
    
    if (!is_numeric($countLeft) or intval($countLeft) < -1 or intval($countLeft) == 0)
    {
        $countLeft = -1;
    }
    
    $countLeft = intval($countLeft);
    
    // save class and function
    $data = unserialize($job -> __get('data'));
    
    if ($function)
    {
        $data['function'] = $function;
    }
    
    
    if (@$_POST['jobname'])
    {
        $job -> jobname = $_POST['jobname'];
    }
    
    $job -> minute = $_POST['minute'];
    $job -> hour = $_POST['hour'];
    $job -> day = $_POST['day'];
    $job -> month = $_POST['month'];
    $job -> weekday = $_POST['weekday'];
    $job -> year = $_POST['year'];
    $job -> count_left = $countLeft;
    $job -> enabled = intval($_POST['enabled']);
    
    try {
        $job -> save();
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', 'message' => slocalize('Cannot save cronjob, details: %s', 'crontab', $e -> getMessage())));
    }
    
    ajax_exit(array('status' => 'success'));
}



/**
  * Remove a crontab job
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'removeJob' or $_GET['action'] == 'toggleEnabled')
{
    $test = new crontab('jobid', $_POST['jobid']);
    
    if (!$test -> exists())
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Selected job does not exists', 'crontab')));
    }
    
    if ($_GET['action'] == 'removeJob')
    {
        // this error should never happen (unless database is broken etc.)
        if (!crontab::removeJob($_POST['jobid']))
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove selected job, unknown error', 'crontab')));
        }
    } elseif ($_GET['action'] == 'toggleEnabled') {
        $test -> enabled = !$test -> enabled;
        $test -> save();
    }
    
    ajax_exit(array('status' => 'success'));
}

/**
  * Editing job details form
  *
  * @author Damian Kęska
  */


if ($_GET['action'] == 'jobDetails')
{

    if (isset($_GET['jobname']))
    {
        $job = new crontab('jobname', $_GET['jobname']);
    } else {
        $job = new crontab('jobid', intval($_GET['jobid']));
    }
    
    if (!$job->exists())
    {
        $noAccess = new uiNoAccess; 
        $noAccess -> display();
    }
    
    $data = unserialize($job -> __get('data'));
    
    $function = $data['function'];
    $class = '';
    
    if (is_array($data['function']))
    {
        $class = $data['function'][0];
        $function = $data['function'][1];
    }
    
    $left = $job->count_left;

    if ($left == -1)
    {
        $left = localize('infinitely', 'crontab');
    }
    
    $timing = array();
    
    foreach ($data['timing'] as $key => $value)
    {
        $timing[date('G:i d.m.Y', $key)] = $value;
    }
    
    $jobDetails = array(
        'id' => $job->jobid,
        'name' => $job->jobname,
        'next_iteration' => date($panthera -> dateFormat, $job->next_interation),
        'crontab_string' => $job->minute. ' ' .$job->hour. ' ' .$job->day. ' ' .$job->month. ' ' .$job->year. ' ' .$job->weekday,
        'count_left' => $left,
        'count_executed' => $job->count_executed,
        'created' => date($panthera -> dateFormat, strtotime($job->created)),
        'function' => $function,
        'class' => $class,
        'log' => $job->log,
        'timing' => $timing,
        'minute' => $job->minute,
        'hour' => $job->hour,
        'day' => $job->day,
        'month' => $job->month,
        'year' => $job->year,
        'weekday' => $job->weekday,
        'enabled' => $job->enabled,
    );
    
    $exp = explode(',', $_GET['removeOptions']);
    
    foreach ($exp as $option)
    {
        if (isset($jobDetails[$option]))
        {
            unset($jobDetails[$option]);
        }
    }
    
    $cron = Cron\CronExpression::factory($jobDetails['crontab_string']);
    $time = time();
    $runtimes = array();
    
    $max = 15;
    
    if ($job->count_left > 0)
    {
        $max = $job -> count_left;
    }
    
    for ($i = 0; $i < $max; $i++)
    {
        $time = $cron -> getNextRunDate(new DateTime('@' .($time+1)));
        $time = $time->getTimeStamp();
        $runtimes[] = date($panthera -> dateFormat, $time);
    }
    
    new uiTitlebar(slocalize('Editing crontab job id #%s', 'crontab', $job->jobid));
    $panthera -> template -> push('runtimes', $runtimes);
    $panthera -> template -> push('timing', $timing);
    $panthera -> template -> push('cronjob', $jobDetails);
    
    if (isset($_GET['popup']))
    {
        $panthera -> template -> display('crontab.popup.tpl');
    } else {
        $panthera -> template -> display('crontab_job.tpl');
    }
    
    pa_exit();
}

/**
  * List of all cronjobs
  *
  * @param string name
  * @return mixed 
  * @author Damian Kęska
  */
 
new uiTitlebar(localize('Scheduled jobs management - crontab', 'crontab'));
  
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
        'next_iteration' => date($panthera -> dateFormat, $job->next_interation),
        'crontab_string' => $job->minute. ' ' .$job->hour. ' ' .$job->day. ' ' .$job->month. ' ' .$job->year. ' ' .$job->weekday,
        'count_left' => $left,
        'count_executed' => $job->count_executed,
        'created' => date($panthera -> dateFormat, strtotime($job->created)),
        'enabled' => $job -> enabled,
    );
}

$panthera -> template -> push('autoloadClasses', $panthera -> config -> getKey('autoloader'));
$panthera -> template -> push('cronjobs', $jobs);
$panthera -> template -> display('crontab.tpl');
pa_exit();
