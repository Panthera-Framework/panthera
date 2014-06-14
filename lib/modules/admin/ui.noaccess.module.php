<?php
/**
  * Admin UI: No access dialog
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Admin UI: No access dialog
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */

class uiNoAccess
{
    protected $panthera;
    protected $settings = array(
        'loggedIn' => False,
        'message' => '',
        'metas' => array()
    );

    public function __construct($message='')
    {
        global $panthera;
        $this -> panthera = $panthera;
        $this->settings['message'] = $message;

        if ($panthera->user)
        {
            $this->settings['loggedIn'] = True;
        }
    }

    /**
      * Add meta attributes to list
      *
      * @param array $array of attributes eg. array('test', 'aaa')
      * @return bool
      * @author Damian Kęska
      */

    public function addMetas($array, $overwrite=False)
    {
        if ($overwrite)
        {
            $this->settings['metas'] = $array;
            return True;
        }

        $this->settings['metas'] = array_merge($this->settings['metas'], $array);
        return True;
    }

    /**
      * Generate data and display no_access.tpl
      *
      * @return void
      * @author Damian Kęska
      */

    public function display()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' and $_SERVER['HTTP_X_REQUESTED_WITH'])
        {
            if (!$this->settings['message'])
            {
                if ($this->settings['loggedIn'])
                {
                    $this->settings['message'] = localize('No permissions to execute this action', 'login');
                } else {
                    $this->settings['message'] = localize('You\'r session propably expired, please re-sign in', 'login');
                }
            }

            ajax_exit(array('status' => 'failed', 'message' => $this->settings['message']));
        }

        @header('HTTP/1.1 403 Forbidden');
        $this -> panthera -> template -> push ('uiNoAccess', $this->settings);

        try {
            $this -> panthera -> template -> display('no_access.tpl');
        } catch (Exception $e) {
            $this -> panthera -> logging -> output('Cannot find no_access.tpl template, triggering standard 403 error', 'uiNoAccess');
            $this -> panthera -> raiseError('forbidden', $this->settings['metas']);
        }
        pa_exit();
    }
}