<?php
/**
  * Panthera Installer core class
  *
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
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
        
        if (!is_dir(SITE_DIR. '/content/installer'))
            mkdir(SITE_DIR. '/content/installer');
        
        if (!is_file(SITE_DIR. '/content/installer/db.json'))
        {
            $fp = fopen(SITE_DIR. '/content/installer/db.json', 'w');
            fwrite($fp, '');
            fclose($fp);
        }
        
        // merge webroot if not merged
        if (!is_dir(SITE_DIR. '/images') or !is_dir(SITE_DIR. '/js') or !is_dir(SITE_DIR. '/css'))
            $panthera -> template -> webrootMerge(array('installer', 'admin'));
        
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
            }
        }

        if (!$this -> panthera -> moduleExists('installer/' .$step))
        {
            $step = 'error_no_step';
        }
        
        // include step
        define('PANTHERA_INSTALLER', True);
        $this -> panthera -> importModule('installer/' .$step);
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
            $this -> panthera -> template -> display ($this->template. '.tpl');
        else
            $this -> panthera -> template -> display('layout.tpl');
    }
    
}
