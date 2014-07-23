<?php
class premiumJobs
{
    /**
     * Cleanup expired premium accounts
     * 
     * @param mixed $data Optional argument used by crontab
     * @return int
     */
    
    public static function deactivateExpiredPackages($data='')
    {
        $filter = new whereClause;
        $filter -> add('AND', 'expires', '<', DB_TIME_NOW);
        
        $accounts = userPremiumAccount::fetchAll($filter);
        $i = 0;
        
        foreach ($accounts as $account)
        {
            $i++;
            $account -> deactivate(); // delete(); cannot be, as we will not have history
        }
        
        return $i;
    }
    
    /**
     * Activate accounts that would be activated in future
     * 
     * @param mixed $data Optional argument used by crontab
     * @return int
     */
    
    public static function startAwaitingPackages($data='')
    {
        $filter = new whereClause;
        $filter -> add('AND', 'expires', '>', DB_TIME_NOW);
        $filter -> add('AND', 'requiresstart', '=', 1);
        $filter -> add('AND', 'starts', '<=', DB_TIME_NOW);
        
        $accounts = userPremiumAccount::fetchAll($filter);
        
        $i = 0;
        
        foreach ($accounts as $account)
        {
            $i++;
            $account -> activate();
            $account -> save();
        }
        
        return $i;
    }
}
