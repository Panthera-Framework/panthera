<?php
/**
  * Cronjobs widget
  *
  * @param string 
  * @return mixed 
  * @author Damian Kęska
  */
  
if (!defined('IN_PANTHERA'))
    exit;
  
class cronjobs_dashWidget extends pantheraClass
{
    //public $template = 'cronjobs';

    public function display()
    {
        $this->panthera->importModule('crontab');
        
        $jobs = crontab::getJobs('', 10);
        
        $jobsTpl = array();
                
        foreach ($jobs as $job)
        {
            $current = new DateTime();
            $next = new DateTime(date($this -> panthera -> dateFormat, $job->next_interation));
            $interval = $current->diff($next);
            $leftInterval = str_replace('0d ', '', $interval->format('%R%dd %hh %im'));
            
            $jobsTpl[] = array('id' => $job->jobid, 'name' => $job -> jobname, 'timeleft' => $leftInterval, 'crontime' => $job->minute. ' ' .$job->hour. ' ' .$job->day. ' ' .$job->month. ' ' .$job->year. ' ' .$job->weekday, 'count' => $job->count_executed);
        }
        
        $this -> panthera -> template -> push ('cronjobsWidgetJobs', $jobsTpl);
    }
}
