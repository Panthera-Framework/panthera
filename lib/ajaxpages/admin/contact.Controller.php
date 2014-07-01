<?php
/**
 * Contact page configuration
 *
 * @package Panthera\core\components\contact
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */
 
pageController::$searchFrontControllerName = 'contactAjaxControllerSystem';

/**
 * Contact page configuration
 *
 * @package Panthera\core\components\contact
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */
 
class contactAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.contact' => array('Contact page management', 'contactpage'),
    );

    protected $language = '';
    protected $fieldName = 'contact.lang.all';

    protected $uiTitlebar = array(
        'Street adress, phone number, location etc.', 'contactpage',
    );

    /**
     * Main function
     *
     * @feature admin.contact.language &$language Contact page language
     * @feature admin.contact.beforedisplay &$controllerObject Before template display
     * @author Damian Kęska
     * @return null
     */

    public function display()
    {
        $this -> panthera -> locale -> loadDomain('contactpage');

        $this -> language = $this -> panthera -> locale -> getFromOverride($_GET['language']);
        $locales = $this -> panthera -> locale -> getLocales();

        if (isset($_GET['language']))
        {
            if (array_key_exists($_GET['language'], $locales))
                $this -> language = $_GET['language'];
        }
        
        // plugins
        $this -> getFeatureRef('admin.contact.language', $this -> language);

        $this -> panthera -> template -> push ('languages', $locales);
        $this -> panthera -> template -> push ('selected_language', $language);

        // set contact page to be one for all languages
        $this -> panthera -> config -> setKey('contact.generic', isset($_POST['all_langs']), 'bool', 'contact');

        // if using one contact page for all languages
        if ($this -> panthera->config->getKey('contact.generic', False, 'bool', 'contact'))
        {
            $this -> fieldName = 'contact.lang.all';
            $this -> panthera -> template -> push ('oneContactPage', True);
        } else {
            $this -> fieldName = 'contact.lang.' .$this->language;
        }

        $contactDefaults = array(
            'text' => '<p>Paste contact data here</p>',
            'map' => '{"bounds":{"ea":{"b":-70.36767265248001,"d":85.11399811539391},"ia":{"b":-180,"d":180}},"zoom":1,"center":{"lb":37.18446862061821,"mb":19.53367485000003}}',
            'mail' => 'example@example.com'
        );

        $contactData = $this -> panthera -> config -> getKey($this->fieldName, $contactDefaults, 'array', 'contact');

        $html = str_replace("\n", '\\n', $this->panthera->config->getKey($fields['contact_text'], '', 'string', 'contact'));
        $html = str_replace("\r", '\\r', $html);
        $html = htmlspecialchars($html, ENT_QUOTES);
        
        if (!defined('CONTACT_SKIP_MAP'))
        {
            if ($contactData['map'] == NULL)
                $map_data = json_decode(stripslashes($contactDefaults['map']), true);
            else
                $map_data = json_decode(stripslashes($contactData['map']), true);

            $x = $map_data['center'][key($map_data['center'])];
            $y = end($map_data['center']);

            $zoom = $map_data['zoom'];

            if (!$x)
                $x = 0;

            if (!$y)
                $y = 0;

            if (!$zoom)
                $zoom = 1;
            
            $this -> template -> push(array(
                'map_zoom' => $zoom,
                'map_x' => $x,
                'map_y' => $y,
            ));
            
        } else
            $this -> template -> push ('skip_map', True);

        $this -> dispatchAction();

        // titlebar
        $this -> uiTitlebarObject -> setTitle(localize('Street adress, phone number, location etc.', 'contactpage') ." (".$this->language.")");
        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/contact.png', 'left');

        // push all variables to template
        $this -> template -> push(array(
            'gmapsApiKey' => $this -> panthera -> config -> getKey('contact.gmaps_key', '', 'string', 'contact'),
            'contact_mail' => $contactData['mail'],
            'adress_text' => htmlspecialchars(str_replace("\n", ' ', $contactData['text'])),
            'contactLanguage' => $this->language,
        ));
        
        $this -> getFeatureRef('admin.contact.beforedisplay', $this);
        return $this -> panthera -> template -> compile('contact.tpl');
    }

    /**
     * Save contact informations
     *
     * @feature admin.contact.save &$_POST Before save
     * @feature admin.contact.save.failure &$controllerObject On save failure
     * @feature admin.contact.save.success &$controllerObject On save success
     * @author Damian Kęska
     * @return null
     */

    public function saveAction()
    {
        $contactDefaults = array(
            'text' => '<p>Paste contact data here</p>',
            'map' => '{"bounds":{"ea":{"b":-70.36767265248001,"d":85.11399811539391},"ia":{"b":-180,"d":180}},"zoom":1,"center":{"lb":37.18446862061821,"mb":19.53367485000003}}',
            'mail' => 'example@example.com'
        );
        
        $this -> getFeatureRef('admin.contact.save', $_POST);

        $contactData = $this -> panthera -> config -> getKey($this->fieldName, $contactDefaults, 'array', 'contact');
        $mdata = $_POST['map_bounds'];
        $contact_text = $_POST['address_text'];
        $email = $_POST['contact_email'];

        if (filter_var($email, FILTER_VALIDATE_EMAIL))
            $contactData['mail'] = $email;

        if(strlen($contact_text) > 0)
            $contactData['text'] = $contact_text;

        if (!defined('CONTACT_SKIP_MAP'))
        {
            // set map location data
            if(!debugTools::isJson($mdata))
            {
                $this -> getFeatureRef('admin.contact.save.failure', $this);
                
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Invalid map location data', 'contactpage'),
                ));
            }

            $contactData['map'] = $mdata;
        }

        // save data right back to database
        $this -> panthera -> config -> setKey($this->fieldName, $contactData, 'array', 'contact');
        $this -> getFeatureRef('admin.contact.save.success', $this);

        ajax_exit(array(
            'status' => 'success',
            'field' => $fieldName,
        ));
    }
}