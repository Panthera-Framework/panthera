<?php
/**
  * Simple contact page module functions
  *
  * @package Panthera\modules\contactpage
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

global $panthera;

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

    if ($panthera->config->getKey('contact_generic', False, 'bool')) {
        $fieldName = 'contact_all';
        $panthera -> template -> push ('oneContactPage', True);
    } else {
        $fieldName = 'contact_' .$language;
    }

    $contactDefaults = array('text' => '<p>Paste contact data here</p>',
                             'map' => '{"bounds":{"Z":{"b":50.52538601346569,"d":50.78657485494268},"fa":{"b":17.58736379609377,"d":18.27400930390627}},"zoom":10,"center":{"jb":50.65616198748283,"kb":17.93068655000002}}',
                             'mail' => 'example@example.com');

    return $panthera->config->getKey($fieldName, $contactDefaults, 'array');
}