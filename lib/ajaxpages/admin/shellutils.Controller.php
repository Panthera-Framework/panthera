<?php
/**
 * Executing shell commands from site
 *
 * @package Panthera\core\adminUI\debug\shellutils
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Executing shell commands from site
 *
 * @package Panthera\core\adminUI\debug\shellutils
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class shellutilsAjaxControllerSystem extends pageController
{
	protected $permissions = array('admin.shellutils' => array('Shell utils', 'debug'));
	protected $uiTitlebar = array(
		'Shell utils', 'debug',
	);

    /**
     * Main function
     *
     * @return null
     */

	public function display()
	{
		$shellCommands = array(
		    'ps u' => 'ps u',
		    'debug.py' => PANTHERA_DIR. '/tools/debug.py',
		    'w' => 'w',
		    'whoami' => 'whoami',
		    'ls -la ~' => 'ls -la ~',
		    'uptime' => 'uptime',
		    'users' => 'users',
		    'ping google.com' => '/bin/ping google.com -c 2',
		    'ls -la .' => 'ls -la .',
		    'pwd' => 'pwd',
		    'uname -a' => 'uname -a'
		);

		switch ($_GET['exec'])
		{
		    case 'debug.py':
		        try {
		            $output = file_get_contents(SITE_DIR. '/content/tmp/debug.log');

		            ajax_exit(array(
		            	'status' => 'success',
		            	'message' => nl2br($output),
				    ));
		        } catch (Exception $e) {
		            ajax_exit(array(
		              'status' => 'failed',
		              'message' => localize('Cannot execute shell command'),
                    ));
		        }
		    break;

		    case 'ping google.com':
		        try {
		            $output = shell_exec('/bin/ping google.com -c 2');

		            ajax_exit(array(
		                'status' => 'success',
		                'message' => nl2br($output),
                    ));
		        } catch (Exception $e) {
		            ajax_exit(array(
		              'status' => 'failed',
		              'message' => localize('Cannot execute shell command'),
                    ));
		        }
		    break;

		    default:
		        if (isset($shellCommands[$_GET['exec']]))
		        {
		            try {
		                $output = shell_exec($shellCommands[$_GET['exec']]);
		                ajax_exit(array(
		                  'status' => 'success',
		                  'message' => nl2br($output),
                        ));
		            } catch (Exception $e) {
		                ajax_exit(array(
		                  'status' => 'failed',
		                  'message' => localize('Cannot execute shell command'),
                        ));
		            }
		        }
		    break;
		}

		// titlebar
		$this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/Apps-yakuake-icon.png', 'left');

		// template
		$this -> panthera -> template -> push('commands', $shellCommands);
		return $this -> panthera -> template -> compile('shellutils.tpl');
	}
}