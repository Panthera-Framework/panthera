<?php
/**
  * Contact page configuration
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_edit_contact'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

// localization
$panthera -> locale -> loadDomain('contactpage');
$language = $panthera -> locale -> getFromOverride($_GET['language']);
$locales = $panthera -> locale -> getLocales();

if (isset($_GET['language']))
{
    if (array_key_exists($_GET['language'], $locales))
        $language = $_GET['language'];
}

$panthera -> template -> push ('languages', $locales);
$panthera -> template -> push ('selected_language', $language);

// set contact page to be for all languages
if ($_GET['action'] == 'save')
{
    if (isset($_POST['all_langs']))
        $panthera -> config -> setKey('contact.generic', True, 'bool');
    else {
        $panthera -> config -> setKey('contact.generic', False, 'bool');
    }
}


// if using one contact page for all languages
if ($panthera->config->getKey('contact.generic', False, 'bool'))
{
    $fieldName = 'contact.lang.all';
    $panthera -> template -> push ('oneContactPage', True);
} else {
    $fieldName = 'contact.lang.' .$language;
}


$contactDefaults = array(
    'text' => '<p>Paste contact data here</p>', 
    'map' => '{"bounds":{"ea":{"b":-70.36767265248001,"d":85.11399811539391},"ia":{"b":-180,"d":180}},"zoom":1,"center":{"lb":37.18446862061821,"mb":19.53367485000003}}', 
    'mail' => 'example@example.com'
);

$contactData = $panthera -> config -> getKey($fieldName, $contactDefaults, 'array');

/**
  * Save all contact informations
  *
  * @author Damian Kęska
  */
  
if ($_GET['action'] == 'save')
{
    $mdata = $_POST['map_bounds'];
    $contact_text = $_POST['address_text'];
    $email = $_POST['contact_email'];
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $contactData['mail'] = $email;
    }
    
    if(strlen($contact_text) > 0)
        $contactData['text'] = $contact_text;

    if (!defined('CONTACT_SKIP_MAP'))
    {
        // set map location data
        if(!isJson($mdata))
        {
            ajax_exit(array('status' => 'failed', 'error ' => localize('Invalid map location data', 'contactpage')));
        }
        
        $contactData['map'] = $mdata;
    }
    
    // save data right back to database
    $panthera -> config -> setKey($fieldName, $contactData, 'array');

    ajax_exit(array('status' => 'success', 'field' => $fieldName, 'message' => localize('Saved')));
    pa_exit();
}

$html = str_replace("\n", '\\n', $panthera->config->getKey($fields['contact_text']));
$html = str_replace("\r", '\\r', $html);
$html = htmlspecialchars($html, ENT_QUOTES);

if (!defined('CONTACT_SKIP_MAP'))
{
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
        
    $template -> push ('map_zoom', $zoom);
    $template -> push ('map_x', $x);
    $template -> push ('map_y', $y);
} else
    $template -> push ('skip_map', True);

$panthera -> template -> push ('gmapsApiKey', $panthera -> config -> getKey('gmaps_key', '', 'string'));
$panthera -> template -> push ('contact_mail', $contactData['mail']);
$panthera -> template -> push ('adress_text', htmlspecialchars(str_replace("\n", ' ', $contactData['text'])));
$panthera -> template -> push ('contactLanguage', $language);

$titlebar = new uiTitlebar(localize('Street adress, phone number, location etc.', 'contactpage') ." (".$language.")");
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/contact.png', 'left');

$template -> display('contact.tpl');
pa_exit();
