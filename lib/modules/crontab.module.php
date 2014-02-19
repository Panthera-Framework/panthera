<?php
/**
  * Scheduled tasks management
  * 
  * @package Panthera\modules\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
// YES! We are using third-party library
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/CronExpression.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/FieldInterface.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/AbstractField.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/FieldFactory.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/MinutesField.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/HoursField.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/DayOfMonthField.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/MonthField.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/DayOfWeekField.php');
include (PANTHERA_DIR. '/share/cron-expression/src/Cron/YearField.php');

class crontab extends pantheraFetchDB
{
    protected $_tableName = 'cronjobs';
    protected $_idColumn = 'jobid';
    protected $_constructBy = array('jobid', 'jobname', 'id', 'array');
    
    /**
      * Get job data
      *
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function getData($serialize=False)
    {
        return unserialize($this->__get('data'));
    }
    
    /**
      * Get interval expression in crontab-compatible syntax
      *
      * @return string 
      * @author Damian Kęska
      */
    
    public function getIntervalExpression()
    {
        return $this->__get('minute'). ' ' .$this->__get('hour'). ' ' .$this->__get('day'). ' ' .$this->__get('month'). ' ' .$this->__get('year'). ' ' .$this->__get('weekday');
    }
    
    /**
      * Set job data
      *
      * @param mixed data
      * @return void
      * @author Damian Kęska
      */
    
    public function setData($data)
    {
        $this->__set('data', serialize($data));
    }

    /**
	 * Lock the job to avoid duplication
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function lock()
    {
        $SQL = $this->panthera->db->query('UPDATE `{$db_prefix}cronjobs` SET `lock` = :time WHERE `jobid` = :jobid', array('jobid' => $this->__get('jobid'), 'time' => time()));
        return True;
    }

    /**
	 * Unlock job after finish
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function unlock()
    {
        $SQL = $this->panthera->db->query('UPDATE `{$db_prefix}cronjobs` SET `lock` = :time WHERE `jobid` = :jobid', array('jobid' => $this->__get('jobid'), 'time' => 0));
        return True;
    }

    /**
	 * Check if job is locked
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function locked()
    {
        $SQL = $this->panthera->db->query('SELECT `lock` FROM `{$db_prefix}cronjobs` WHERE `jobid` = :jobid', array('jobid' => $this->__get('jobid')));
        $Array = $SQL->fetch();
        return (bool)$Array['lock'];
    }

    /**
	 * Execute job code
	 *
	 * @return string
	 * @author Damian Kęska
	 */

    public function execute()
    {
        //print("Execute: ".$this->jobname."\n");
        
        // don't start a disabled job
        if (!$this -> enabled)
        {
            return False;
        }
        
        // we are starting, so the start time should be resetted
        $this -> start_time = 0;
        $t = microtime_float();
    
        if (intval($this->__get('count_left')) == 0)
            return "JOB_FINISHED";

        //if ($this->locked() == False)
        //{
            //$this->lock();
            $data = unserialize($this->_data['data']);
            $includedFiles = get_included_files();

            $filePath = getContentDir($data['file']);
            $this->panthera->logging->output('crontab::Including ' .$filePath, 'crontab');
            
            if (!$filePath)
            {
                $filePath = $data['fullFileName'];
            }
            
            if (!is_file($filePath))
                return 'ERR_CANNOT_INCLUDE_FILE';
                
            //print("Including: ".$filePath."\n");
            
            include_once $filePath;
            
            $return = '';
            
            //print("Handler: ".print_r($data['function'])."\n");

            if (is_array($data['function']))
            {
                if (!class_exists($data['function'][0]))
                    return 'ERR_NO_SUCH_CLASS: ' .$data['function'][0];

                $refl = new ReflectionMethod($data['function'][0], $data['function'][1]);

                if (!$refl)
                    return 'ERR_NO_SUCH_METHOD: ' .$data['function'][1];

                // static methods
                if ($refl -> isStatic())
                {
                    $return = $data['function'][0]::$data['function'][1]($data['data'], $this);
                } else { 
                    // dynamic methods
                    $o = new $data['function'][0]();

                    if (!method_exists($o, $data['function'][1]))
                        return 'ERR_NO_SUCH_METHOD: ' .$data['function'][1];

                    $return = $o -> $data['function'][1]($data['data'], $this);
                }

            } else {
                if (!function_exists($data['function']))
                    return 'ERR_NO_SUCH_FUNCTION: ' .$data['function'];

                $return = $data['function']($data['data'], $this);
            }
            
            
            $this->__set('count_executed', ($this->__get('count_executed')+1));
            
            // finished jobs should be removed
            if ($return == 'FINISHED' or $return == 'JOB_FINISHED')
            {
                $this->__set('count_left', "0");
                return 'JOB_FINISHED';
            }
            
            if ($this->__get('count_left') != -1)
                $this->__set('count_left', (intval($this->__get('count_left'))-1));
                
            $this -> updateExecutionTimeStats(microtime_float()-$t);
            $this -> log = $this -> panthera -> logging -> getOutput();
                
            return $return;
            // $this->unlock();
        //}
    }
    
    /**
      * Update job execution time statistics
      *
      * @param float $time
      * @return void
      * @author Damian Kęska
      */
    
    protected function updateExecutionTimeStats($time)
    {
        $data = unserialize($this->__get('data'));
        
        if (!isset($data['timing']))
        {
            $data['timing'] = array();
        }
        
        if (!isset($data['maxtiming']))
        {
            $data['maxtiming'] = 15;
        }
        
        $maxTiming = $data['maxtiming'];
        
        if (count($data['timing']) >= $maxTiming)
        {
            ksort($data['timing']);
            reset($data['timing']);
            unset($data['timing'][key($data['timing'])]);
        }
        
        $data['timing'][time()] = $time;
        $this -> __set('data', serialize($data));
    }

    /**
	 * Regenerate `next_interation` unix timestamp
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    public function regenerateInterationTime()
    {
        // generate `next_interation` unix timestamp
        if ($this->__get('hour') == '')
            return False;
            
        $this->panthera->logging->output('crontab::Regenerating time for crontab: ' .$this->__get('minute'). ' ' .$this->__get('hour'). ' ' .$this->__get('day'). ' ' .$this->__get('month'). ' ' .$this->__get('weekday'). ' ' .$this->__get('year'), 'crontab');

        $cron = Cron\CronExpression::factory($this->__get('minute'). ' ' .$this->__get('hour'). ' ' .$this->__get('day'). ' ' .$this->__get('month'). ' ' .$this->__get('weekday'). ' ' .$this->__get('year'));
        
        $startTime = 0;
        
        if (intval($this->start_time) != 0)
            $startTime = date($this -> panthera -> dateFormat, $this->start_time);

        if (intval($this->start_time) != 0)
        {
            $time = $cron -> getNextRunDate($startTime);
            //$this->start_time = 0;
        } else
            $time = $cron -> getNextRunDate();
            
        $time = $time->getTimestamp();
        
        //var_dump($time->getTimestamp());
            
        if ($time != $this->__get('next_interation'))
            $this->__set('next_interation', $time);
    }

    /**
	 * Save data, generate `next_interation` unix timestamp
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    public function save()
    {
        if ($this->__get('hour') == '')
            return False;

        $this->regenerateInterationTime();
        // execute save function
        parent::save();
    }
    
    /**
      * Mark job for deletion (set count_left to 0)
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function finish()
    {
        $this->count_left = 0;
    }

    /**
	 * Get locked jobs with lock timeout set in first argument (this function should be used to cleanup jobs that just crashed)
	 *
     * @param int $timeout Timeout after the job should be back
	 * @return string
	 * @author Damian Kęska
	 */


    public static function getLockedJobs($timeout=3600)
    {
        global $panthera;

        $time = (time()-$timeout);

        $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}cronjobs` WHERE `lock` <= :time', array('time' => $time));

        $jobs = array();

        foreach ($SQL->fetchAll() as $key => $job)
        {
            $jobs[] = new crontab('array', $job);
        }

        return $jobs;
    }

    /**
	 * Get jobs
	 *
     * @param array $by DB Columns
     * @param int $limit SQL Limit
     * @param int $limitFrom SQL limit position
     * @param string $sortBy Column to sort by
     * @param string $sortHow ASC or DESC
	 * @return string
	 * @author Damian Kęska
	 */


    public static function getJobs($by='', $limit=0, $limitFrom=0, $sortBy='jobid', $sortHow='DESC')
    {
        global $panthera;
        return $panthera->db->getRows('cronjobs', $by, $limit, $limitFrom, 'crontab', $sortBy, $sortHow);  
    }

    /**
	 * Get all expired jobs
	 *
	 * @return array
	 * @author Damian Kęska
	 */

    public static function getJobsForWork()
    {
        global $panthera;
        $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}cronjobs` WHERE `next_interation` <= :time', array('time' => time()));

        $jobs = array();

        foreach ($SQL->fetchAll() as $key => $job)
        {
            $jobs[] = new crontab('array', $job);
        }

        return $jobs;
    }

    /**
	 * Remove specified cron job
	 *
     * @param int $jobid
	 * @return bool
	 * @author Damian Kęska
	 */

    public static function removeJob($jobid)
    {
        global $panthera;
        
        $job = new crontab('jobid', $jobid);
        
        if (!$job -> exists())
        {
            return True;
        }
        
        $job -> clearCache();
        $panthera -> logging -> output ('removeJob jobid=' .$jobid, 'crontab');
        $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}cronjobs` WHERE `jobid` = :jobid', array('jobid' => $jobid));
        return (bool)$SQL->rowCount();
    }
    

    /**
	 * Create new planned job
	 *
     * @param string $jobname Job name
     * @param mixed $function Function name (as string) or array in format array(string Classname, string Method)
     * @param mixed $data Data to be passed to hooked function
     * @param string $minute Minute (default: *) crontab format, eg. 10, eg. 8-10, eg. 10,11
     * @param string $hour Hour (default: *) crontab format, eg. 10, eg. 8-10, eg. 10,11 
     * @param string $day Day (default: *) crontab format, eg. 10, eg. 8-10, eg. 10,11
     * @param string $month Month (default: *) crontab format, eg. 10, eg. 8-10, eg. 10,11
     * @param string $dayOfWeek Day of week (default: *) crontab format, eg. 0 for Monday
     * @param string year Year eg. 2013 (cant be in past)
	 * @return bool
	 * @author Damian Kęska
	 */

    public static function createJob($jobname, $function, $data, $minute='*', $hour='*', $day='*', $month='*', $dayOfWeek='*', $year='*', $enabled)
    {
        global $panthera;
        
        if (is_array($function))
        {
            // autoload specified class from autoloader
            $autoloader = $panthera -> config -> getKey('autoloader');
        
            if (isset($autoloader[$function[0]]) and !class_exists($function[0]))
            {
                $panthera -> importModule($autoloader[$function[0]]);
            }
        
            // check if class and it's method exists
            if(class_exists($function[0]))
            {
                $refl = new ReflectionMethod($function[0], $function[1]);

                if (!$refl)
                    throw new Exception('Method ' .$function[1]. '() of class ' .$function[0]. ' does not exists');
            } else
                throw new Exception('Class ' .$function[0]. ' does not exists');
        } else {
            if (!function_exists($function))
                throw new Exception('Function ' .$function. '() does not exists');

            $refl = new ReflectionFunction($function);
        }

        if (is_file($refl->getFileName()))
            $fileName = $refl->getFileName();


        $panthera -> logging -> output('crontab::Create from syntax: ' .$minute. ' ' .$hour. ' ' .$day. ' ' .$month. ' ' .$dayOfWeek. ' ' .$year, 'crontab');
        $cron = Cron\CronExpression::factory($minute. ' ' .$hour. ' ' .$day. ' ' .$month. ' ' .$dayOfWeek. ' ' .$year);
        $time = $cron -> getNextRunDate();
        $time = $time->getTimeStamp();

        $array = array('data' => serialize(array(
            'function' => $function, 
            'data' => $data, 
            'file' => str_replace(PANTHERA_DIR, '', $fileName), 
            'fullFileName' => $fileName)), 
            'jobname' => $jobname, 
            'minute' => $minute, 
            'hour' => $hour, 
            'day' => $day, 
            'month' => $month, 
            'weekday' => $dayOfWeek, 
            'year' => $year, 
            'next_interation' => $time,
            'enabled' => intval($enabled),
        );

        $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}cronjobs` (`jobid`, `jobname`, `data`, `minute`, `hour`, `day`, `month`, `year`, `weekday`, `next_interation`, `created`, `enabled`) VALUES (NULL, :jobname, :data, :minute, :hour, :day, :month, :year, :weekday, :next_interation, NOW(), :enabled)', $array);
        
        return (bool)$SQL->rowCount();
    }
    
    /**
      * Get default intervals
      *
      * @param string $intervalName to get expression for
      * @return array
      * @author Damian Kęska
      */
    
    public static function getDefaultIntervals($intervalName='')
    {
        $options = array();
        $options['1m'] = array('title' => slocalize('%s minutes', 'messages', 1), 'expression' => '*/1 * * * * *');
        $options['2m'] = array('title' => slocalize('%s minutes', 'messages', 2), 'expression' => '*/2 * * * * *');
        $options['5m'] = array('title' => slocalize('%s minutes', 'messages', 5), 'expression' => '*/5 * * * * *');
        $options['10m'] = array('title' => slocalize('%s minutes', 'messages', 10), 'expression' => '*/10 * * * * *');
        $options['15m'] = array('title' => slocalize('%s minutes', 'messages', 15), 'expression' => '*/15 * * * * *');
        $options['30m'] = array('title' => slocalize('%s minutes', 'messages', 30), 'expression' => '*/30 * * * * *');
        $options['45m'] = array('title' => slocalize('%s minutes', 'messages', 45), 'expression' => '*/45 * * * * *');
        $options['50m'] = array('title' => slocalize('%s minutes', 'messages', 50), 'expression' => '*/50 * * * * *');
    
        // hours
        for ($i=1; $i <= 24; $i++)
        {
            $options[$i. 'h'] = array('title' => slocalize('%s hours', 'messages', $i), 'expression' => '* */' .$i. ' * * * *');
        }
        
        // days
        for ($i=1; $i <= 31; $i++)
        {
            $options[$i. 'd'] = array('title' => slocalize('%s days', 'messages', $i), 'expression' => '* * */' .$i. ' * * *');
        }
        
        $options['1o'] = array('title' => slocalize('%s months', 'messages', 1), 'expression' => '* * * */1 * *');
        $options['2o'] = array('title' => slocalize('%s months', 'messages', 2), 'expression' => '* * * */2 * *');
        $options['3o'] = array('title' => slocalize('%s months', 'messages', 3), 'expression' => '* * * */3 * *');
        $options['6o'] = array('title' => slocalize('%s months', 'messages', 6), 'expression' => '* * * */6 * *');
        $options['8o'] = array('title' => slocalize('%s months', 'messages', 8), 'expression' => '* * * */8 * *');
        $options['10o'] = array('title' => slocalize('%s months', 'messages', 10), 'expression' => '* * * */10 * *');
        $options['12o'] = array('title' => slocalize('%s months', 'messages', 12), 'expression' => '* * * */12 * *');
        
        if ($intervalName)
        {
            if (isset($options[$intervalName]))
            {
                return $options[$intervalName];
            }
            
            return '7d';
        }
        
        return $options;
    }
}

class cronjobs
{
    /**
	 * Built-in crontab function to fix all jobs that crashed
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    public static function unlockCrashedJobs($data='')
    {
        global $panthera;

        $timeout = (time()-$panthera->config->getKey('crontab_timeout', '3600', 'int'));
        $jobs = crontab::getJobs('');

        foreach ($jobs as $job)
        {
            // delete finished jobs
            if (intval($job->count_left) == 0)
            {
                print("Job finished: ".$job->jobname."\n");
                crontab::removeJob($job->jobid);
                unset($job);
                continue;
            }

            if (intval($job->lock) <= $timeout and $job -> lock)
            {
                echo "Unlock: ".$job->jobname."\n";
                $job->unlock();
            }

            if ($job->next_interation < $currentTime)
            {
                $job -> regenerateInterationTime();
                print("Found a job with bad `next_interation` time\n");
            }
            
            // regenerateInterationTime and save
            $job->save();
        }
    }

    /**
	 * Cleanup run sockets
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    public static function cleanRunSockets($data='')
    {
        global $panthera;
        $panthera -> db -> query('DELETE FROM `{$db_prefix}run` WHERE `expired` < :expiretime', array('expiretime' => (microtime(true)-15)));
    }
    
    /**
	 * Built-in crontab function to optimize in-memory table that takes too much RAM memory after some time
	 *
	 * @return void
	 * @author Damian Kęska
	 */
	 
	 public static function optimizeRunTable($data='')
	 {
	    global $panthera;
	    print("Optimizing ".$panthera -> db -> prefix."_run table.");
	    
	    if ($panthera->db->getSocketType() == 'mysql')
	    {
	        try {
	            $panthera -> db -> query('ALTER TABLE `{$db_prefix}_run` ENGINE=MEMORY;');
	        } catch (Exception $e) { /* pass */ }
        } else
            print("Not using MySQL, so... skipping...");
	 }
	 
    /**
	 * Built-in crontab function to remove not activated subscriptions within X days
	 *
	 * @return void
	 * @author Damian Kęska
	 */
	 
	 public static function removeExpiredSubscriptions ($data='')
	 {
	    global $panthera;

        $days = $panthera -> config -> getKey('newsletter_expire', 2, 'int');
	    try {
	        $panthera -> db -> query('DELETE FROM `{$db_prefix}newsletter_users` WHERE `activate_id` != "" AND `added` < NOW() - INTERVAL ' .$days. ' DAYS');
	    } catch (Exception $e) { /* pass */ }
	 }
	
    /**
	 * Built-in crontab function to remove expired password recovery requests
	 *
	 * @return void
	 * @author Damian Kęska
	 */
	 
	 public static function removeExpiredPasswdRecovery($data='')
	 {
	    global $panthera;

        $days = $panthera -> config -> getKey('passwd_rec_expire', 7, 'int');
	    try {
	        $panthera -> db -> query('DELETE FROM `{$db_prefix}password_recovery` WHERE `type` = "recovery" AND `date` < now() - interval :days days', array('days' => $days));
	    } catch (Exception $e) { /* pass */ }
	 }
}


