<?php
/**
 * Panthera Installer core class
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

require_once PANTHERA_DIR. '/pageController.class.php';

/**
 * Panthera Installer core class
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class pantheraInstaller
{
    public $template = null;
    public $appConfig = null;
    public static $instance = null;

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
        self::$instance = $this;

        if (!($index = getContentDir('installer/config.json')))
            throw new Exception('Cannot find /lib/installer/config.json (check Panthera installation integrity), and /lib/installer/config.json');

        $panthera -> importModule('rwjson');
        $panthera -> importModule('libtemplate');
        $panthera -> importModule('appconfig');

        if (!is_dir(SITE_DIR. '/content/installer'))
            mkdir(SITE_DIR. '/content/installer');

        if (!is_file(SITE_DIR. '/content/installer/db.json'))
        {
            $fp = fopen(SITE_DIR. '/content/installer/db.json', 'w');
            fwrite($fp, '');
            fclose($fp);
        }

        // merge webroot if not merged
        if (!is_dir(SITE_DIR. '/images') or (time()-filemtime(SITE_DIR. '/images') < 3600) or !isset($_COOKIE[md5(SITE_DIR). '_webroot']))
        {
            libtemplate::webrootMerge(array(
                'installer' => 1,
                'admin' => 1,
            ));

            setCookie(md5(SITE_DIR). '_webroot', time(), time() + 3600);
        }

        // temporary database for installer
        $this -> config = (object)json_decode(file_get_contents($index));

        if (!isset($this->config->steps))
            throw new Exception('Installer configuration must contain list of steps');

        $this -> db = new writableJSON(SITE_DIR. '/content/installer/db.json', $this -> config);

        $steps = $this -> db -> steps;

        // set first step as current if no current step already set
        if (!$this -> db -> currentStep)
            $this -> db -> set('currentStep', $this -> db -> steps[0]);

        if ($this -> db -> holdThisStep)
            $this -> db -> set('currentStep', $this -> db -> holdThisStep);

        /* Localization */
        // save locale to configuration file
        if (isset($_GET['_locale']))
            $this -> db -> set('locale', $_GET['_locale']);

        // restore locale from configuration file
        if ($this -> db -> exists('locale') and !isset($_GET['_locale']))
        {
            $this -> panthera -> locale -> addLocale(strtolower($this -> db -> locale));
            $this -> panthera -> locale -> setLocale(strtolower($this -> db -> locale));
        }

        /* Installation steps */
        // enable or disable back button
        $currentStepKey = array_search($this->db->currentStep, $this->db->steps);

        if ($currentStepKey > 0)
            $this->setButton('back', True);

        // default title
        $panthera -> template -> setTitle(localize('Panthera Framework installer', 'installer'));


        /* Title */
        // title from installer database db.json
        if ($this -> db -> exists('installerTitle'))
            $panthera -> template -> setTitle($this -> db -> installerTitle);

        if (is_file(SITE_DIR. '/content/app.php'))
            $this -> appConfig = new appConfigEditor();
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
                $this -> panthera -> template -> push('installerBackBtn', (bool)$state);
            break;

            case 'next':
                $this -> panthera -> template -> push('installerNextBtn', (bool)$state);
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
     * @config lockStep (Debugging) Lock step, so we can't move to next step
     * @return void
     * @author Damian Kęska
     */

    public function loadStep()
    {
        $step = $this->db->currentStep;

        if (isset($_GET['_stepbackward']) and !$this -> db -> lockStep)
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

        if (isset($_GET['_nextstep']) and !$this -> db -> lockStep)
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

/**
 * Default front controller for pantheraInstaller
 *
 * @package Panthera\installer
 * @author Damian Kęska
 */

class installerController extends pageController
{
    public $installer = null;
    public $appConfig = null;
    
    public function __construct()
    {
        parent::__construct();
        $this -> installer = pantheraInstaller::$instance;
        $this -> appConfig = $this -> installer -> appConfig;
    }

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
        );

        foreach ($controllerNames as $className)
        {
            if (class_exists($className))
                return $className;
        }
    }
}