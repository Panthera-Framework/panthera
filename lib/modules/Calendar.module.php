<?php
/**
 * Calendar - Month view generator
 * 
 * @package Panthera\core\modules\calendar
 * @author Damian Kęska <webnull.www@gmai.com>
 * @license LGPLv3
 */
 
/**
 * Calendar - Month view generator
 * 
 * Example:
 * <code>
 * $calendar = new calendarMonthView('2014-05-20');
 * var_dump($calendar -> build());
 * </code>
 * 
 * @package Panthera\core\modules\calendar
 * @author Damian Kęska <webnull.www@gmai.com>
 */

class calendarMonthView
{
    public $calendar = array(
    
    );
    
    /**
     * Constructor
     * 
     * @param string $date Input date (eg. 30.06.2012 or 2014-05-20)
     * @author Damian Kęska <webnull.www@gmai.com>
     * @return null
     */
    
    public function __construct($date='')
    {
        if (!$date)
            $date = date('Y-m-d');
        
        $this -> timestamp = strtotime($date);
        
        $this -> calendar = array(
            'monthDays' => cal_days_in_month(CAL_GREGORIAN, date('m', $this -> timestamp), date('Y', $this -> timestamp)),
            'currentMonth' => date('m', $this -> timestamp),
            'currentYear' => date('Y', $this -> timestamp),
        );
    }
    
    /**
     * Get weekday number for date or english day name
     * 
     * @param string $date Input formatted date
     * @param bool $getWeekDay (Optional) Get weekday number
     * @return string|int
     */
    
    public static function getWeekdayForDate($date, $getWeekDay=False)
    {
        $dateTime = new DateTime($date);
        
        if (!$getWeekDay)
            return $dateTime -> format('D');
        
        $englishDays = array(
            'Mon' => 1,
            'Tue' => 2,
            'Wed' => 3,
            'Thu' => 4,
            'Fri' => 5,
            'Sat' => 6,
            'Sun' => 7,
        );
        
        return $englishDays[$dateTime -> format('D')];
    }
    
    /**
     * Build calendar array
     * 
     * @author Damian Kęska <webnull.www@gmai.com>
     * @return array
     */
    
    public function build()
    {
        $maxDay = ((7*6)+1); // 7 days in 6 rows
        $date = new DateTime($this -> calendar['currentYear']. '-' .$this -> calendar['currentMonth']. '-01');
        $date -> modify('-1 month');
        $this -> calendar['previousMonth'] = $date -> format('m');
        $this -> calendar['previousYear'] = $date -> format('Y');
        $this -> calendar['previousMonthDays'] = cal_days_in_month(CAL_GREGORIAN, $this -> calendar['previousMonth'], $this -> calendar['previousYear']);
        $this -> calendar['previousMonthDate'] = $date -> format('d.m.Y');
        $this -> calendar['rows'] = array();
        $today = date('d.m.Y');
        

        $posStart = self::getWeekdayForDate('01.' .$this -> calendar['currentMonth']. '.' .$this -> calendar['currentYear'], true);
        $this -> calendar['rows'][0] = array();
        $date = new DateTime('01.' .$this -> calendar['currentMonth']. '.' .$this -> calendar['currentYear']);
        $date -> modify('-' .$posStart. ' days');
        
        // get end position
        $tmp = clone $date;
        $tmp -> modify('+' .$maxDay. ' days');
        $posMax = $tmp -> format('d.m.Y');
        
        // calculate next month date
        $tmp = clone $date;
        $tmp -> modify('+2 months');
        $this -> calendar['nextMonth'] = $tmp -> format('m');
        $this -> calendar['nextYear'] = $tmp -> format('Y');
        //$tmp -> modify('-1 months'); 
        $this -> calendar['nextMonthDate'] = $tmp -> format('d.m.Y');
        
        $rowPos = -1;
        $rowID = 0;
        
        $threeMonths = array(
            $this -> calendar['previousMonth'] => 'previousMonth',
            $this -> calendar['currentMonth'] => 'currentMonth',
            $this -> calendar['nextMonth'] => 'nextMonth',
        );
        
        for ($i=1; $i < $maxDay; $i++)
        {
            $date -> modify('+1 day');
            $timestamp = $date -> getTimestamp();
            $rowPos++;
            
            if ($rowPos == 7)
            {
                $rowID++;
                $rowPos = 0;
            }
            
            $dayName = self::getWeekdayForDate($date -> format('d.m.Y'));
            
            $this -> calendar['rows'][$rowID][$rowPos] = array(
                'dayNum' => $i,
                'day' => $dayName,
                'monthDay' => $date -> format('j'),
                'formatted' => $date -> format('d.m.Y'),
                'timestamp' => $timestamp,
                'type' => $threeMonths[$date -> format('m')],
                'active' => (date('d.m.Y', $this -> timestamp) == $date -> format('d.m.Y')),
                'weekend' => ($dayName == 'Sat' or $dayName == 'Sun'),
                'sunday' => ($dayName == 'Sun'),
            );
        }

        return $this -> calendar;
    }
}