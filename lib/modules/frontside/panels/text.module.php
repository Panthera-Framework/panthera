<?php
/**
  * Simple text panel
  *
  * @package Panthera\modules\frontside\panels
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Simple panel showing only selected text
  *
  * @package Panthera\modules\frontside\panels
  * @author Damian Kęska
  */

class frontsidePanel_text
{
    public function display($data)
    {
        global $panthera;
        
        if (!$data['template'])
        {
            $data['template'] = 'text.tpl';
        }
        
        $login = '';
        $userName = '';
        $userID = '';
        
        if ($panthera->user)
        {
            $userName = $panthera -> user -> getName();
            $login = $panthera -> user -> login;
            $userID = $panthera -> user -> id;
        }
        
        $text = pantheraLocale::selectStringFromArray($data['info']['storage']['text']);
        
        // user name, login, id etc.
        $text = str_ireplace('{$userName}', $userName, $text);
        $text = str_ireplace('{$login}', $login, $text);
        $text = str_ireplace('{$userID}', $userID, $text);
        
        if (stripos($text, '{$query}'))
        {
            $text = str_ireplace('{$query}', getQueryString('GET', '_', ''), $text);
        }
        
        // informations about user's browser
        $clientInfo = $panthera -> session -> get('clientInfo');
        $text = str_ireplace('{$browser}', $clientInfo['browser'], $text);
        $text = str_ireplace('{$system}', $clientInfo['os'], $text);
        $text = str_ireplace('{$deviceType}', $clientInfo['deviceType'], $text);

        //$panthera -> template -> push('panelMenu', $menu->show());
        return $panthera -> template -> display('panels/' .$data['template'], True, '', array('text' => $text));
    }
}
