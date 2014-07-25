<?php
$depth = 0;
$lastID = null;

global $GLOBALS;

if (!isset($_SERVER['print']))
    $_SERVER['print'] = True;

$GLOBALS['categoriesSelect'] = array();

function categoriesTpl_getCategory($z)
{
    global $lastID;
    global $depth;
    global $categoriesSelect;
    global $print;
    global $GLOBALS;
    
    if (!$z['item'])
        return;
    
    if ($lastID and $lastID -> categoryid != $z['item']->parentid)
        $depth = 0;
    
    $lastID = $z['item'];
    $depth++;
    
    $liBefore = '';
    $liAfter = '';
    
    if ($_GET['objectID'] == $z['item'] -> categoryid)
    {
        $liBefore = '<i><b>';
        $liAfter = '</b></i>';
    }
    
    if ($_SERVER['print'])
        print('<li><div style="padding-left: ' .(($depth*4)). 'px;">' .$liBefore. '<a href="?' .Tools::getQueryString('GET', 'categoryType=' .$z['item'] -> categoryType. '&objectID=' .$z['item']->categoryid, '_'). '" class="ajax_link" title="' .localize('Priority', 'categories'). ': ' .$z['item'] -> priority. '">' .$z['item'] -> title. '</a>' .$liAfter. '</div></li>');

    $GLOBALS['categoriesSelect'][$z['item']->categoryid] = str_repeat('--', $depth). ' ' .$z['item'] -> title;
    
    foreach ($z['subcategories'] as $b)
        categoriesTpl_getCategory($b, $depth);
}

