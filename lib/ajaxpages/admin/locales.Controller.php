<?php
/**
 * Locales management for Panthera Framework
 *
 * @package Panthera\core\system\locale
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Locales management for Panthera Framework
 *
 * @package Panthera\core\system\locale
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

class localesAjaxControllerCore extends pageController
{
   	protected $userPermissions = array();

    protected $uiTitlebar = array(
        'Manage site localization', 'locales'
    );

    protected $permissions = 'can_update_locales';

	/**
     * Set language as default
     *
     * @author Mateusz Warzyński
     * @return null
     */

	public function setAsDefaultAction()
	{
		$this -> panthera -> locale -> setSystemDefault($_POST['id']);
		$systemDefault = $this -> panthera -> locale -> getSystemDefault();
	}



	/**
     * Delete language
     *
     * @author Mateusz Warzyński
     * @return null
     */

	public function deleteAction()
	{
		$this -> panthera -> locale -> removeLocale($_POST['id']);
	}



	/**
     * Add language
     *
     * @author Mateusz Warzyński
     * @return null
     */

	public function addAction()
	{
		if (is_dir(SITE_DIR.'/content/locales/'.$_POST['id']) or is_dir(PANTHERA_DIR. '/locales/' .$_POST['id']))
        	$this -> panthera -> locale -> addLocale($_POST['id']);
	}



	/**
     * Save category details to database
     *
     * @author Mateusz Warzyński
     * @return null
     */

	public function toggleVisibilityAction()
	{
		$visibility = $this->locales[$_POST['id']];
        $this -> panthera -> locale -> toggleLocale($_POST['id'], !$visibility);
	}


    /**
     * Displays main locales template
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        $this -> locales = $this -> panthera -> locale -> getLocales();
		$this -> systemDefault = $this -> panthera -> locale -> getSystemDefault();

		$this -> dispatchAction();

		$tmp = scandir(SITE_DIR. '/content/locales/');
		$tmpLib = scandir(PANTHERA_DIR. '/locales/');
		$tmp = array_merge($tmp, $tmpLib);

		$avaliableLocales = array();
		$avaliableLocales[] = 'english';

		foreach ($tmp as $value)
		{
		    if (array_key_exists($value, $locales))
		        continue;

		    if ($value == ".." or $value == "." or $value == "nocache")
		        continue;

		    if(is_dir(SITE_DIR. '/content/locales/' .$value) or is_dir(PANTHERA_DIR. '/locales/' .$value))
		        $avaliableLocales[] = $value;
		}

		$this->locales = array();

		foreach ($this->panthera->locale->getLocales() as $locale => $visibility)
		{
		    $this->locales[$locale]['visibility'] = $visibility;

		    if (is_file(SITE_DIR. '/images/admin/flags/' .$locale. '.png'))
		        $this->locales[$locale]['flag'] = TRUE;
		    else
		        $this->locales[$locale]['flag'] = FALSE;
		}

		$this -> panthera -> template -> push('locales_dir', $avaliableLocales);

		$this -> panthera -> template -> push('locales_added', $this->locales);
		$this -> panthera -> template -> push('loaded_domains', $this->panthera->locale->getLoadedDomains());
		$this -> panthera -> template -> push('action', $_GET['action']);

		$this -> panthera -> template -> push('locale_system_default', $this->systemDefault);

        return $this -> panthera -> template -> compile('locales.tpl');
    }
}