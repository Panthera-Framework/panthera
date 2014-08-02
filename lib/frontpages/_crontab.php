<?php
/**
 * Panthera crontab front controller
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @license LGPLv3
 */

// dont load these components to make this application faster
//define('SKIP_SESSION', True);
//define('SKIP_TEMPLATE', True);

require_once 'content/app.php';
include_once getContentDir('pageController.class.php');

@set_time_limit(0);

/**
 * Panthera crontab front controller
 *
 * Executes scheduled jobs. Should be executed every one minute by operating system's crontab.
 * Can be used from shell using php _crontab.php or via url including ?_appkey=$YOUR_APP_KEY (see crontab_key in configuration)
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 */

class _crontabControllerSystem extends pageController
{
    /**
     * Add Panthera Framework system default cronjobs (those jobs can be manually disabled or this function can be forked)
     *
     * @author Damian Kęska
     * @return null
     */

    public function createDefaultJobs()
    {
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

        // clean up database var_cache
        try {crontab::createJob('db_varcache', array('cronjobs', 'cleanupDBvarCache'), '', '*/15'); } catch (Exception $e) {}

        // clean up files var cache
        try {crontab::createJob('files_varcache', array('cronjobs', 'cleanupFilesvarCache'), '', '*/45'); } catch (Exception $e) {}
        
        // minfy HTML code
        try {crontab::createJob('minify_all', array('minifyJob', 'minifyAll'), '', '0', '22', '*/1'); } catch (Exception $e) {}
    }

    /**
     * Main function
     *
     * @author Damian Kęska
     * @return null
     */

    public function display()
    {
        $this -> panthera -> config -> loadOverlay('crontab');
        $this -> panthera -> importModule('crontab');

        // dont mess debug.log file
        $this -> panthera -> logging -> debug = True;
        $this -> panthera -> logging -> tofile = false;
        /*$panthera -> logging -> filterMode = 'blacklist';
        $panthera -> logging -> filter['crontab'] = True;
        $panthera -> logging -> filter['pantheraFetchDB'] = True;*/

        $this -> createDefaultJobs();
        $this -> checkCrontabKey();
        
        if (isset($_GET['debug']))
        {
            error_reporting(E_ALL);
            $this -> panthera -> logging -> tofile = True;
        }
        
        $this -> startCrontab();
    }

    /**
     * Do the crontab job
     *
     * @return null
     */

    public function startCrontab()
    {
        // create Panthera socket to show in "process list" (ptop and crontop tools uses Panthera sockets)
        run::openSocket('crontab', intval(getmypid()), array('client' => $_SERVER['REMOTE_ADDR'], 'url' => $_SERVER['REQUEST_URI'], 'user' => 'system'));

        // get all expired jobs to start working
        $jobs = crontab::getJobsForWork();
        
        if (isset($_GET['api']) and $_GET['api'] == 'json' and isset($_GET['action']) and $_GET['action'] == 'list')
        {
            $links = array();
            
            foreach ($jobs as $job)
                $links[$job->jobname] = pantheraUrl('{$PANTHERA_URL}/_crontab.php?_appkey=' .$_GET['_appkey']. '&jobname=' .$job->jobname);
            
            
            die(json_encode(array(
                'status' => 'success',
                'links' => $links,
            )));
        }

        // cont the jobs
        $jobsCount = 0;

        try {
            if (isset($_GET['jobname']))
            {
                $j = new crontab('jobname', $_GET['jobname']);
    
                if ($j -> exists())
                {
                    print("Starting job: ".$j->jobname."\n");
                    $this -> startJob($j);
                    $jobsCount++;
                }
            } else {
                foreach ($jobs as $key => $job)
                {
                    $jobsCount++;
                    print("Starting job: ".$job->jobname."\n");
                    $this -> startJob($job);
                    $job->save();
                }
            }
        } catch (Exception $e) {
            $this -> panthera -> logging -> output('Catched exception during job execution: ' .$e -> getMessage(), 'crontab');
        }

        if ($jobsCount == 0)
            print("No work to do.");
        else {
            // save log to file if this option is enabled in config as "crontab_log"
            if ($this -> panthera -> config -> getKey('crontab_log', True, 'bool'))
            {
                if (is_writable(SITE_DIR. '/content/tmp/crontab.log'))
                {
                    $fp = @fopen(SITE_DIR. '/content/tmp/crontab.log', 'w');
                    fwrite($fp, ob_get_contents());
                    fclose($fp);
                } else
                    print("ERROR: CANNOT SAVE LOG! NO WRITE PERMISSIONS IN " .SITE_DIR. "/content/tmp/crontab.log\n");
            }

            if (isset($_GET['_debugsession']))
                print_r("\n\n\n".$this -> panthera -> logging -> getOutput());
        }

        // unlock all crashed jobs
        cronjobs::unlockCrashedJobs();

        // close crontab session
        run::closeSocket('crontab', intval(getmypid()));
    }

    /**
     * Check crontab authorization key
     *
     * @return null
     */

    public function checkCrontabKey()
    {
        if (PANTHERA_MODE == 'CLI')
            return True;
        
        $key = $_GET['_appkey'];
        
        if ($key != $this -> panthera -> config -> getKey('crontab_key', generateRandomString(64), 'string'))
        {
            header('HTTP/1.1 403 Forbidden');
            pa_exit();
        }
    }

    /**
     * Start a job
     *
     * @feature crontab.job.pre-run &object $job Job object
     * @feature crontab.job.post-run &object $job Job object
     * @param object $job
     * @return null
     * @author Damian Kęska
     */

    public function startJob($job)
    {
        $this -> getFeatureRef('crontab.job.pre-run', $job);

        if (!$job -> enabled)
            return False;

        if ($job -> locked() == False)
        {
            $job -> lock();
            $text = '';

            try {
                $text = $job -> execute();
                $job->save();
            } catch (Exception $e) {
                print("Cannot execute job: ".$job->jobname."\n");
            }

            if ($text)
                print("Job returned code: ".$text."\n");

            // mark finished job as done
            if ((is_string($text) and strtolower($text) == 'job_finished'))
                $job -> finish();

            $job -> unlock();
            $job -> save();

         } else
            print("Job already locked: ".$job->jobname."\n");

         $this -> getFeatureRef('crontab.job.post-run', $job);
    }
}

// this code will run this controller only if this file is executed directly, not included
pageController::runFrontController(__FILE__, '_crontabControllerSystem');