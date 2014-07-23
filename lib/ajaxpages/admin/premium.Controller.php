<?php
class premiumAjaxControllerSystem extends dataModelManagementController
{
    protected $permissions = array(
        'admin.premium', 'admin.premium.moderate',
    );
    
    protected $uiTitlebar = array(
        'Premium accounts management', 'places',
    );
    
    protected $__dataModelClass = 'userPremiumAccount';
    protected $__baseTemplate = 'premium.tpl';
    protected $__newTemplate = 'premium.new.tpl';
    protected $__editTemplate = 'premium.new.tpl';
    protected $__defaultDisplay = 'list';
    protected $__listId = 'premiumid';
    protected $__modelIdColumn = 'id';
    protected $__searchBarQueryColumns = array(
        'additionalfield1', 'additionalfield2', 'starts', 'expires',
    );
    
    /**
     * Validate user card
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function validateUserCardAction()
    {
        $results = null;
        
        if (isset($_POST['card']) and $_POST['card'])
        {
            $results = userPremiumAccount::fetchAll(array(
                'additionalfield1' => $_POST['card'],
            ), 0, 1);
            
            if (!$results)
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => slocalize('No user found matching %s', 'premium', $_POST['card']),
                ));
            }
            
            $user = new pantheraUser('id', $results[0] -> userid);
            
        } elseif (isset($_POST['username']) and $_POST['username']) {
            
            $filter = new whereClause;
            $filter -> add('OR', 'full_name', 'LIKE', '%' .$_POST['username']. '%');
            $filter -> add('OR', 'login', 'LIKE', '%' .$_POST['username']. '%');
            $filter -> add('OR', 'mail', 'LIKE', '%' .$_POST['username']. '%');
            $filter -> add('OR', 'jabber', 'LIKE', '%' .$_POST['username']. '%');
            
            $user = pantheraUser::fetchAll($filter, 0, 1);
            
            if (!$user)
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => slocalize('No user found matching %s', 'premium', $_POST['username']),
                ));
            }
            
            $results = userPremiumAccount::fetchAll(array(
                'userid' => $user[0] -> id,
            ), 0, 1);
            
            $user = $user[0];
        }
        
        if ($results)
        {
            if ($_POST['resultType'] == 'success')
            {
                ajax_exit(array(
                    'status' => 'success',
                    'user' => $user -> getData(),
                    'account' => $results[0] -> getData(),
                ));
            } else {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => slocalize('The card is valid until %s, it\'s owner is %s (%s). The card number is %s.', 'premium', $results[0] -> expires, $user->getName(), $user -> login, $results[0] -> additionalfield1),
                ));
            }
        }
        
        ajax_exit(array(
            'status' => 'failed',
            'message' => slocalize('No user found matching %s', 'premium', $_POST['username']),
        ));
    }
    
    /**
     * Modify date via ajax request (eg. add +30 days to specified date)
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function modifyDateAction()
    {
        if (!isset($_POST['date']) or !isset($_POST['modifier']))
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('No date or modifier setup', 'premium'),
            ));
            
        $format = 'Y-m-d H:i:s';
        
        if (isset($_POST['format']) and $_POST['format'])
            $format = $_POST['format'];
        
        if (!$_POST['date'])
            $_POST['date'] = date('Y-m-d H:i:s');
        
        if (!$_POST['modifier'])
        {
            ajax_exit(array(
                'status' => 'success',
                'newDate' => date($format, strtotime($_POST['date'])),
            ));
        }

        ajax_exit(array(
            'status' => 'success',
            'newDate' => Tools::userFriendlyStringToDate($_POST['modifier'], $format, $_POST['date']),
        ));
    }
    
    /**
     * Insert premium types to template before showing template
     * 
     * @author Damian Kęska
     * @return mixed
     */
    
    public function editAction()
    {
        $this -> template -> push(array(
            'premiumTypes' => premiumAccountType::fetchAll(''),
            'activatePermissions' => $this -> checkPermissions('admin.premium.moderate', True),
        ));
        
        return parent::editAction();
    }
    
    /**
     * Validate object before saving
     * 
     * @author Damian Kęska
     * @param array &$data POST data
     * @return null
     */
    
    public function validateObjectModification(&$data)
    {
        // for security reasons we prefer login
        unset($data['object_userid']);
        
        $user = new pantheraUser('login', $data['object_userlogin']);
        
        if (!$user -> exists())
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('No valid user specified', 'premium'),
            ));
        }

        if (!panthera::getModel('premiumAccountType') -> premiumid($data['object_premiumid']) -> exists())
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid premium type specified', 'premium'),
            ));
        }
        
        // only administrator can 
        if (!$this -> checkPermissions('admin.premium', true))
            unset($data['object_active']);
        
        if (strtotime($data['object_expires']) < strtotime($data['object_starts']))
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Start time should be before expiration', 'premium'),
            ));
        }
        
        $data['object_userid'] = $user -> id;
    }
    
    /**
     * Validate data before adding new object
     * 
     * @author Damian Kęska
     * @param array &$data POST data
     * @return null
     */
    
    public function validateNewObject(&$data)
    {
        if (isset($data['userlogin']))
        {
            $user = new pantheraUser('login', $data['userlogin']);
            $data['userid'] = $user -> id;
            unset($data['userlogin']);
        } else
            $user = new pantheraUser('id', $data['userid']);
            
        if (!$user -> exists())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid user login or id', 'premium'),
            ));
            
        $premium = new premiumAccountType('premiumid', $data['premiumid']);
        
        if (!$premium -> exists())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid premium type specified', 'premium'),
            ));

        // if current user only has moderative rights
        if ($data['active'] and !$this -> checkPermissions('admin.premium', true))
            $data['active'] = False;

        if (strtotime($data['expires']) < time())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Expiration time is in the past', 'premium'),
            ));
    }
}