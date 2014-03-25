<?php
/**
 * Panthera Installer core class
 *
 * @package Panthera\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */
 
require_once PANTHERA_DIR. '/pageController.class.php';

/**
 * Panthera Installer core class
 *
 * @package Panthera\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */
 
class pantheraInstaller
{
    public $template = null;

    /**
     * Constructor
     *
     * @param panthera $panthera
     * @return void 
     * @author Damian Kęska
     */

    public function __construct($panthera)
    {
        $this -> panthera = $panthera;
        
        if (!($index = getContentDir('installer/config.json')))
        {
            throw new Exception('Cannot find /lib/installer/config.json (check Panthera installation integrity), and /lib/installer/config.json');
        }
        
        $panthera -> importModule('rwjson');
        $panthera -> importModule('libtemplate');
        
        if (!is_dir(SITE_DIR. '/content/installer'))
            mkdir(SITE_DIR. '/content/installer');
        
        if (!is_file(SITE_DIR. '/content/installer/db.json'))
        {
            $fp = fopen(SITE_DIR. '/content/installer/db.json', 'w');
            fwrite($fp, '');
            fclose($fp);
        }
        
        // merge webroot if not merged
        if (!is_dir(SITE_DIR. '/images') or (time()-filemtime(SITE_DIR. '/images') < 3600))
            libtemplate::webrootMerge(array('installer' => 1, 'admin' => 1));
		
        // temporary database for installer
        $this -> config = (object)json_decode(file_get_contents($index));
        
        if (!isset($this->config->steps))
            throw new Exception('Installer configuration must contain list of steps');
        
        $this -> db = new writableJSON(SITE_DIR. '/content/installer/db.json');
        
        // set first step as current if no current step already set
        if (!$this->db->currentStep)
        {
            $this->db->currentStep = $this->config->steps[0];
        }
        
        // enable or disable back button
        $currentStepKey = array_search($this->db->currentStep, $this->config->steps);
        
        if ($currentStepKey > 0)
        {
            $this->setButton('back', True);
        }
        
        $panthera -> template -> setTitle(localize('Panthera Framework installer', 'installer'));
    }
    
    /**
     * Set button state
     *
     * @param string $button
     * @param bool $state
     * @return bool 
     * @author Damian Kęska
     */
    
    public function setButton ($button, $state)
    {
        switch ($button)
        {
            case 'back':
                $this->panthera->template->push('installerBackBtn', (bool)$state);
            break;
            
            case 'next':
                $this->panthera->template->push('installerNextBtn', (bool)$state);
            break;
            
            default:
                return False;
            break;
        }
        
        return True;
    }
    
    /**
     * Try to go to next step (header redirection)
     * 
     * @return null
     */
    
    public function goToNextStep()
    {
        header('Location: ?_nextstep=True');
        pa_exit();
    }
    
    /**
     * Load a step, detect backward and forward moving parameters
     *
     * @return void 
     * @author Damian Kęska
     */
    
    public function loadStep()
    {
        $step = $this->db->currentStep;
        
        if (isset($_GET['_stepbackward']))
        {
            $currentStepKey = array_search($this->db->currentStep, $this->config->steps);
            
            if ($currentStepKey > 0)
            {
                $currentStepKey--;
                $step = $this->config->steps[$currentStepKey];
                $this -> db -> currentStep = $step;
                $this -> db -> save();
            }
        }
        
        if (isset($_GET['_nextstep']))
        {
            if ($this -> db -> nextStepEnabled)
            {
                $this -> db -> currentStep = $this -> db -> nextStepEnabled;
                $this -> db -> set ('currentStep', $this -> db -> nextStepEnabled);
                $this -> db -> remove('nextStepEnabled');
                $this -> setButton('back', True);
                $step = $this->db->currentStep;
                $this -> db -> save();
            }
        }

        if (!$this -> panthera -> moduleExists('installer/' .$step))
        {
            $step = 'error_no_step';
        }
        
        // include step
        define('PANTHERA_INSTALLER', True);
        $this -> panthera -> importModule('installer/' .$step);
		
		if ($class = installerController::getControllerName($step))
		{
			$object = new $class;
			$object -> installer = $this;
            
            if (method_exists($object, 'prepare'))
                $object -> prepare();
            
			$object -> display();
		}
    }
    
    /**
     * Enable next step if its not the last step
     *
     * @return void 
     * @author Damian Kęska
     */
    
    public function enableNextStep()
    {
        $currentStepKey = array_search($this->db->currentStep, $this->config->steps);
        
        if (($currentStepKey+1) == count($this->config->steps))
        {
            $this -> setButton('back', true);
            $this -> setButton('next', false);
            return False;
        }
        
        $this -> setButton('next', true);
        $this -> db -> nextStepEnabled = $this->config->steps[$currentStepKey+1];
        return True;
    }
    
    /**
     * Display installer's template
     *
     * @return void 
     * @author Damian Kęska
     */
    
    public function display()
    {
        if (!$this->template)
            $this -> template = 'no_page';
    
        $this -> panthera -> template -> push ('stepTemplate', $this->template);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
            $this -> panthera -> template -> display($this->template. '.tpl');
        else
            $this -> panthera -> template -> display('layout.tpl');
    }
}

class installerController extends pageController 
{
    public $installer = null;
	
	/**
     * Lookup for controller class name
     * 
     * @param string $name Controller name
     * @return string|null
     */
	
    public static function getControllerName($name)
    {
        $custom = '____non_existent_controller___';
        
        if (static::$searchFrontControllerName)
            $custom = static::$searchFrontControllerName;
        
        $controllerNames = array(
            $custom,
            $name. 'InstallerController',
            $name. 'InstallerControllerCore',
            $name. 'InstallerControllerSystem',
        );
        
        foreach ($controllerNames as $className)
        {
            if (class_exists($className))
                return $className;
        }
    }
}