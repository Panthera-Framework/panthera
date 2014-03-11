<?php
/**
  * Panthera crontab front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

// dont load these components to make this application faster
//define('SKIP_SESSION', True);
//define('SKIP_TEMPLATE', True);

require_once 'content/app.php';

@set_time_limit(3600);

$panthera -> config -> loadOverlay('crontab');
$panthera -> importModule('crontab');

// dont mess debug.log file
$panthera -> logging -> tofile = true;

// Cleaning up the crashed jobs
//try {crontab::createJob('fix_cron_crash', array('cronjobs', 'unlockCrashedJobs'), '', '*/1'); } catch (Exception $e) {}

// Cleaning up run sockets
try {crontab::createJob('clean_run_sockets', array('cronjobs', 'cleanRunSockets'), '', '*/1'); } catch (Exception $e) {}

// Optimizing _run table
try {crontab::createJob('optimize_run', array('cronjobs', 'optimizeRunTable'), '', '0'); } catch (Exception $e) {}

// Removing expired subscriptions
try {crontab::createJob('expired_subscriptions', array('cronjobs', 'removeExpiredSubscriptions'), '', '0', '0', '*/1'); } catch (Exception $e) {}

// Removing expired password recoveries
try {crontab::createJob('expired_passwd_recovery', array('cronjobs', 'removeExpiredPasswdRecovery'), '', '0', '0', '*/1'); } catch (Exception $e) {}

// Update Panthera Autoloader cache at 23:00 everyday
try {crontab::createJob('autoloader_cache', array('pantheraAutoloader', 'updateCache'), '', '0', '23', '*/1'); } catch (Exception $e) {}

// clean up var_cache
try {crontab::createJob('db_varcache', array('cronjobs', 'cleanupDBvarCache'), '', '*/1'); } catch (Exception $e) {}

// removeExpiredSubscriptions

$key = $_GET['_appkey'];

/**
  * Start a job
  *
  * @param object $job
  * @return void 
  * @author Damian Kęska
  */

function startJob($job)
{
    if (!$job -> enabled)
    {
        return False;
    }
    
    if ($job -> locked() == False)
    {
        $job->lock();
        $text = '';

        try {
            $text = $job->execute();
            $job->save();
        } catch (Exception $e) {
            print("Cannot execute job: ".$job->jobname."\n");
        }

        if ($text)
            print("Job returned code: ".$text."\n");
            
        // mark finished job as done
        if (strtolower($text) == 'job_finished')
            $job -> finish();

        $job->unlock();
        $job->save();
        
     } else
        print("Job already locked: ".$job->jobname."\n");
}

if ($key == $panthera -> config -> getKey('crontab_key', generateRandomString(64), 'string'))
{
    if (isset($_GET['debug']))
    {
        error_reporting(E_ALL);
        $panthera -> logging -> tofile = True;
    }
    
    // create Panthera socket to show in "process list"
    run::openSocket('crontab', intval(getmypid()), array('client' => $_SERVER['REMOTE_ADDR'], 'url' => $_SERVER['REQUEST_URI'], 'user' => 'system'));
    
    $panthera -> logging -> debug = FALSE;
    // get all expired jobs to start working
    $jobs = crontab::getJobsForWork();
    $panthera -> logging -> debug = TRUE;
    
    // cont the jobs
    $jobsCount = 0;

    foreach ($jobs as $key => $job)
    {
        $jobsCount++;
        print("Starting job: ".$job->jobname."\n");
        startJob($job);
        $job->save();
    }

    if ($jobsCount == 0)
        print("No work to do.");
    else {
        // save log to file if this option is enabled in config as "crontab_log"
        if ($panthera->config->getKey('crontab_log', True, 'bool'))
        {
            if (is_writable(SITE_DIR. '/content/tmp/crontab.log'))
            {
                $fp = @fopen(SITE_DIR. '/content/tmp/crontab.log', 'w');
                fwrite($fp, ob_get_contents());
                fclose($fp);
            } else
                print("ERROR: CANNOT SAVE LOG! NO WRITE PERMISSIONS IN " .SITE_DIR. "/content/tmp/crontab.log\n");
        }
    }
    
    // unlock all crashed jobs
    cronjobs::unlockCrashedJobs();

    // mark job as done
    run::closeSocket('crontab', intval(getmypid()));

} else {
    header('HTTP/1.1 403 Forbidden');
}

pa_exit();
