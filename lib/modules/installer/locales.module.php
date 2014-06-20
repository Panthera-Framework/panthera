<?php
/**
 * Locales configuration
 * 
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

if (!defined('PANTHERA_INSTALLER'))
    return False;

/**
 * Locales configuration
 * Edit, set as default
 * 
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 */

class localesInstallerControllerSystem extends installerController
{
    protected $requirements = array(
        'liblangtool',
    );
    
    /**
     * Main function that runs everything
     * 
     * @author Damian Kęska
     */

    public function display()
    {
        $allLocales = localesManagement::getLocales();
        $locales = array();
        
        
        if (isset($_GET['setDefaultLanguage']))
        {
            if (isset($allLocales[$_GET['setDefaultLanguage']]) or $_GET['setDefaultLanguage'] == 'english')
                $this -> panthera -> locale -> setSystemDefault($_GET['setDefaultLanguage']);
        
        } elseif (isset($_GET['switchLanguage'])) {
        
            if (isset($allLocales[$_GET['switchLanguage']]))
            {
                $installedLocales = $this -> panthera -> locale -> getLocales();
        
                if (isset($installedLocales[$_GET['switchLanguage']]))
                    $this -> panthera -> locale -> toggleLocale($_GET['switchLanguage'], !$installedLocales[$_GET['switchLanguage']]);
                else
                    $this -> panthera -> locale -> addLocale($_GET['switchLanguage']);
            }
        }
        
        $installedLocales = $this -> panthera -> locale -> getLocales();
        
        foreach ($allLocales as $locale => $path)
        {
            $locales[$locale] = array('default' => False, 'enabled' => False, 'icon' => False);
        
            // check if there is a flag for this locale
            if (is_file(SITE_DIR. '/images/admin/flags/' .$locale. '.png'))
                $locales[$locale]['icon'] = True;
        
            // check if this language is enabled
            if ($installedLocales[$locale] == True)
                $locales[$locale]['enabled'] = True;
        
            // check if this language is currently set as default
            if ($this -> panthera->locale->getSystemDefault() == $locale)
                $locales[$locale]['default'] = True;
        }
        
        $this -> installer -> enableNextStep();
        $this -> panthera -> template -> push ('defaultLocale', $this -> panthera->locale->getSystemDefault());
        $this -> panthera -> template -> push ('locales', $locales);
        $this -> installer -> template = 'locales';
    }
}