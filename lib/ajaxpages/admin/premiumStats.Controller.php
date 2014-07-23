<?php
include getContentDir('ajaxpages/admin/premium.Controller.php');

class premiumStatsAjaxControllerSystem extends premiumAjaxController
{
    protected $uiTitlebar = array(
        'Premium stats', 'premium',
    );
    
    protected $__baseTemplate = 'premium.stats.tpl';
    
    public function datamodel_premiumStats_list_fetchall_argsFeature($args)
    {
        if (isset($_GET['sortBy']))
        {
            if ($_GET['sortBy'] == 'A-Z')
            {
                $args[3] = 'additionalfield1';
                $args[4] = 'ASC';
            } else {
                $args[3] = 'expires';
                $args[4] = 'DESC';
            }
        }
        
        // MySQL only
        $where = '';
        
        if (isset($_GET['freeUsers']))
        {
            $where = ' WHERE p.premiumid IS NULL';
            
            $query = $this -> panthera -> db -> query('SELECT count(*) FROM `{$db_prefix}users` LEFT JOIN `{$db_prefix}premium_user` as p ON {$db_prefix}users.id = p.userid ' .$where. ';');
            
            $count = $query -> fetchAll();
            $count = $count[0]['count(*)'];
            
            $this -> __pager = new uiPager(get_called_class(), $count, get_called_class(). 'Freeusers', 25);
            
            if ($this -> __pagerTemplatesConfig)
                $this -> __pager -> setLinkTemplatesFromConfig($this -> __pagerTemplatesConfig);
            
            $limit = $this -> __pager -> getPageLimit();
            
            $this -> template -> push('uiPagerName', get_called_class());
        }
        
        $query = $this -> panthera -> db -> query('SELECT *, p.`premiumid` FROM `{$db_prefix}users` LEFT JOIN `{$db_prefix}premium_user` as p ON {$db_prefix}users.id = p.userid ' .$where. '; LIMIT ' .$limit[0]. ',' .$limit[1]. ';');
        $free = 0;
        $premium = 0;
        $fetch = $query -> fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($fetch as $record)
        {
            if (!$record['premiumid'])
            {
                $free++;
            } else
                $premium++;
        }
        
        $this -> template -> push(array(
            'freeUsers' => $fetch,
            'totalFreeUsers' => $count,
        ));
        
        if (isset($_GET['freeUsers']))
            return null;
        
        if (isset($_GET['inactive']))
            $args[0] -> add('AND', 'active', '=', 0);
        else
            $args[0] -> add('AND', 'active', '=', 1);

        $this -> template -> push(array(
            'statsFree' => $free,
            'statsPremium' => $premium,
            'inactive' => isset($_GET['inactive']),
        ));
        
        return $args;
    }
}
