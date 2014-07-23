<?php
class newsletter_recipientsAjaxControllerSystem extends pageController
{
    protected $permissions = 'admin.newsletter.cat.{$nid}';
    protected $newsletter = null;

    protected $uiTitlebar = array(
        'Compose a new message', 'newsletter'
    );
    
    /**
     * Save recipients list to session
     *
     * @return null
     */
    
    public function saveRecipientsAction()
    {
        ajax_exit(array(
            'status' => 'success',
            'groups' => $_POST,
            'json' => json_encode($_POST),
        ));
    }

    public function display()
    {
        /**
         * Cities
         */
        
        $citiesTmp = $this -> db -> getRows('users', '', 0, 0, '', 'id', 'DESC', array('city'));
        $cities = array();
        
        foreach ($citiesTmp as $row)
        {
            if (!$row['city'] or strlen($row['city']) < 3)
                continue;
            
            if (!isset($cities[$row['city']]))
                $cities[$row['city']] = 1;
            else
                $cities[$row['city']]++;  
        }
        
        /**
         * Genders
         */
        
        $genders = array();
        
        foreach (pantheraUser::$genders as $gender)
        {
            $filter = new whereClause;
            $filter -> add('', 'gender', '=', $gender);
            $genders[$gender] = pantheraUser::fetchAll($filter, false);
        }
        
        /**
         * Groups
         */
        
        $groups = array();
        
        foreach (pantheraGroup::fetchAll() as $group)
            $groups[$group -> group_id] = array(
                'name' => $group -> name,
                'count' => $group -> findUsers(False),
                'description' => $group -> description,
            );
            
        /**
         * Premium
         */
         
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
            'cities' => $cities,
            'genders' => $genders,
            'groups' => $groups,
            'premium' => array(
                'premium' => $premium,
                'free' => $free,
            ),
        ));
        
        $this -> dispatchAction();
        return $this -> template -> compile('newsletter.recipients.tpl');
    }
}
