<?php
/**
  * Simple contact page module functions
  *
  * @package Panthera\modules\contactpage
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
 * Get contact page details
 *
 * @return array with contact items eg. text, map, mail
 * @author Damian Kęska
 */

function getContactPage ()
{
    $panthera = pantheraCore::getInstance();
    $language = $panthera->locale->getActive();

    if ($panthera->config->getKey('contact.generic', False, 'bool', 'contact'))
    {
        $fieldName = 'contact.lang.all';
        $panthera -> template -> push ('oneContactPage', True);
    } else {
        $fieldName = 'contact.lang.' .$language;
    }

   $contactDefaults = array(
       'text' => '<p>Paste contact data here</p>',
       'map' => '{"bounds":{"ea":{"b":-70.36767265248001,"d":85.11399811539391},"ia":{"b":-180,"d":180}},"zoom":1,"center":{"lb":37.18446862061821,"mb":19.53367485000003}}',
       'mail' => 'example@example.com',
   );

    return $panthera -> config -> getKey($fieldName, $contactDefaults, 'array', 'contact');
}