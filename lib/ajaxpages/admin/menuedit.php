<?php
/**
 * Manage menus
 *
 * @package Panthera
 * @subpackage core
 * @copyright (C) Damian Kęska, Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit ;

$tpl = 'menuedit.tpl';

if (!getUserRightAttribute($user, 'can_update_menus')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('menuedit');

// menu is provided as module
$panthera -> importModule('simplemenu');

/** JSON PAGES **/

if ($_GET['action'] == 'save_order') 
{
    $order = json_decode($_POST['order']);

    foreach ($order as $orderKey => $id)
        $panthera -> db -> query('UPDATE `{$db_prefix}menus` SET `order`= :orderKey WHERE `id`= :id', array('id' => intval($id), 'orderKey' => intval($orderKey)));

    ajax_exit(array('status' => 'success', 'message' => localize('Order has been successfully saved', 'menuedit')));
}

if ($_GET['action'] == 'quickAddFromPopup') {
    if (substr($_GET['link'], 0, 5) == 'data:')
    {
        $_GET['link'] = base64_decode(substr($_GET['link'], strpos($_GET['link'], 'base64,')+7, strlen($_GET['link'])));    
    }


    $language = $panthera -> locale -> getFromOverride($_GET['language']);
    $categories = simpleMenu::getCategories('');
    $panthera -> template -> push('link', $_GET['link']);
    $panthera -> template -> push('title', $_GET['title']);
    $panthera -> template -> push('currentLanguage', $language);
    $panthera -> template -> push('categories', $categories);
    $panthera -> template -> push('languages', $panthera -> locale -> getLocales());
    $panthera -> template -> display('menuedit_quickaddfrompopup.tpl');
    pa_exit();
}

/**
 *
 * Saving menu elements
 * Returns json
 *
 * @author Mateusz Warzyński
 */

if ($_GET['action'] == 'save_item') {
    // check if areas are empty
    if (!$_POST['cat_type'] or !$_POST['item_title'])
        ajax_exit(array('status' => 'failed', 'message' => localize('Some areas are empty!')));

    $id = intval($_POST['item_id']);

    $item = new menuItem('id', $id);

    if ($item -> exists()) {
        $item -> title = filterInput($_POST['item_title'], 'quotehtml');
        $item -> link = filterInput($_POST['item_link'], 'quotehtml,quotes');

        if ($_POST['item_url_id'] == '' or strlen($_POST['item_url_id']) < 3) {
            // replace ' ' to '-'
            $url_id = str_replace(' ', '-', filterInput($_POST['item_title'], 'quotehtml'));
            // remove special characters from string
            $url_id = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $url_id);
            // change all letters to lower
            $url_id = strtolower($url_id);
        } else {
            $url_id = seoUrl($_POST['item_url_id']);
        }

        $item -> url_id = $url_id;
        $item -> tooltip = filterInput($_POST['item_tooltip'], 'quotehtml');
        $item -> icon = filterInput($_POST['item_icon'], 'quotehtml');

        if (array_key_exists($_POST['item_language'], $panthera -> locale -> getLocales()))
            $item -> language = $_POST['item_language'];

        $item -> attributes = $_POST['item_attributes'];
        $item -> save();
        simpleMenu::updateItemsCount($_POST['cat_type']);

        ajax_exit(array('status' => 'success', 'message' => localize('Item has been successfully saved!')));
    }

    ajax_exit(array('status' => 'failed', 'message' => 'Unhandled error'));
}

/**
 *
 * Adding menu elements
 * Returns json
 *
 * @author Mateusz Warzyński
 */

if ($_GET['action'] == 'add_item') {
    if ($_POST['cat_type'] == '' or $_POST['item_title'] == '')
        ajax_exit(array('status' => 'failed', 'message' => localize('Please enter a title', 'menuedit')));

    $lastItem = simpleMenu::getItems($_POST['cat_type'], 0, 1, 'order', 'desc');

    // if there are any items already stored in database
    if (count($lastItem) > 0) {
        $order = intval($lastItem -> order) + 1;
    } else
        $order = 1;

    if ($_POST['item_url_id'] == '' or strlen($_POST['item_url_id']) < 3) {
        // replace ' ' to '-'
        $url_id = str_replace(' ', '-', filterInput($_POST['item_title'], 'quotehtml'));
        // remove special characters from string
        $url_id = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $url_id);
        // change all letters to lower
        $url_id = strtolower($url_id);

    } else {
        $url_id = seoUrl($_POST['item_url_id']);
    }

    // filter all variables to avoid problems with HTML & JS injection and/or bugs with text inputs
    $title = filterInput($_POST['item_title'], 'quotehtml');
    $link = filterInput($_POST['item_link'], 'quotehtml,quotes');
    $attributes = filterInput($_POST['item_attributes'], 'quotehtml');
    $tooltip = filterInput($_POST['item_tooltip'], 'quotehtml');
    $icon = filterInput($_POST['item_icon'], 'quotehtml');

    if (!array_key_exists($_POST['item_language'], $panthera -> locale -> getLocales()))
        ajax_exit(array('status' => 'failed', 'messages' => localize('Invalid language specified', 'menuedit')));

    $language = $panthera -> locale -> getActive();

    simpleMenu::createItem($_POST['cat_type'], $title, $attributes, $link, $language, $url_id, $order, $icon, $tooltip);
    simpleMenu::updateItemsCount($_POST['cat_type']);
    ajax_exit(array('status' => 'success', 'message' => localize('Item has been successfully added', 'menuedit')));
}

/**
 *
 * Removing menu elements
 * Returns json
 *
 * @author Mateusz Warzyński
 */

if ($_GET['action'] == 'remove_item') {
    $id = intval($_GET['item_id']);

    $item = new menuItem('id', $id);
    simpleMenu::removeItem($item -> id);
    simpleMenu::updateItemsCount($item->type);
    unset($item);
    ajax_exit(array('status' => 'success', 'message' => localize('Item has been successfully removed', 'menuedit')));
}

/**
 *
 * Adding menu categories
 * Returns json
 *
 * @author Mateusz Warzyński
 */

if ($_GET['action'] == 'add_category') {
    // We cannot create category without title or type_name
    if ($_POST['category_type_name'] == '' or $_POST['category_title'] == '')
        ajax_exit(array('status' => 'failed', 'message' => 'Some fields are empty'));

    // filter all variables to avoid problems with HTML & JS injection and/or bugs with text inputs
    $type_name = filterInput($_POST['category_type_name'], 'quotehtml');
    $title = filterInput($_POST['category_title'], 'quotehtml');
    $description = filterInput($_POST['category_description'], 'quotehtml');
    $parent = filterInput($POST['category_parent'], 'quotehtml');
    $elements = filterInput($POST['category_elements'], 'quotehtml');

    if (simpleMenu::createCategory($type_name, $title, $description, intval($parent), intval($elements))) {
        ajax_exit(array('status' => 'success', 'message' => localize('Category has been successfully added', 'menuedit')));
    }

    ajax_exit(array('status' => 'failed', 'message' => 'Unhandled error!'));
}

/**
 *
 * Removing menu categories
 * Returns json
 *
 * @author Mateusz Warzyński
 */

if ($_GET['action'] == 'remove_category') {
    $id = intval($_GET['category_id']);

    $category = new menuCategory('id', $id);

    // check if category exists
    if ($category -> exists()) {
        simpleMenu::removeCategory($category -> id);
        ajax_exit(array('status' => 'success', 'message' => localize('Category has been removed', 'menuedit')));
    }

    ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove category', 'menuedit')));
}

/** END OF JSON PAGES **/

/** Ajax-HTML PAGES **/

// if plugin simpleMenu is disabled
if (!class_exists('menuCategory')) {
    $template -> push('action', 'plugin_disabled');
    $template -> display($tpl);
    pa_exit();
}

// Displaying menu item edit page
if ($_GET['action'] == 'item') {
    $tpl = "menuedit_item.tpl";

    $item = new menuItem('id', intval($_GET['id']));

    if ($item -> exists()) {
        $template -> push('item_title', $item -> title);
        $template -> push('item_url_id', $item -> url_id);
        $template -> push('item_link', $item -> link);
        $template -> push('item_tooltip', $item -> tooltip);
        $template -> push('item_icon', $item -> icon);
        $template -> push('item_attributes', $item -> attributes);
        $template -> push('item_id', $item -> id);
        $template -> push('cat_type', $item -> type);

        $locales = array();

        foreach ($panthera -> locale -> getLocales() as $key => $value) {
            // skip hidden locales
            if ($value == False)
                continue;

            $active = False;

            // if this is a current set locale
            if ($item -> language == $key)
                $active = True;

            $locales[$key] = $active;
        }

        $template -> push('item_language', $locales);
        $template -> push('action', 'item');
        $template -> display($tpl);
        pa_exit();
    }
}

if ($_GET['action'] == 'new_category') {
    $template -> display('menuedit_newcategory.tpl');
    pa_exit();
}

if ($_GET['action'] == 'category') {
    $tpl = "menuedit_category.tpl";

    $items = simpleMenu::getItems($_GET['category']);
    $array = array();

    foreach ($items as $key => $value) {
        $tmp = $value;
        $tmp['link_original'] = $value['link'];
        $tmp['link'] = pantheraUrl($value['link']);
        $array[$key] = $tmp;
    }

    $template -> push('menus', $array);
    $template -> push('category', $_GET['category']);
    $template -> display($tpl);
    pa_exit();
}

if ($_GET['action'] == 'new_item') {
    $tpl = "menuedit_newitem.tpl";

    $locales = array();

    foreach ($panthera -> locale -> getLocales() as $key => $value) {
        // skip hidden locales
        if ($value == False)
            continue;

        $active = False;

        // if this is a current set locale
        if ($item -> language == $key)
            $active = True;

        $locales[$key] = $active;
    }

    $template -> push('item_language', $locales);
    $template -> push('cat_type', $_GET['category']);
    $template -> display($tpl);
    pa_exit();
}

/** END OF Ajax-HTML PAGES **/

// default action is to view all menu categories
$categories = simpleMenu::getCategories('');

$c = array();

foreach ($categories as $key => $value) {
    $c[$value -> id] = array('name' => filterInput($value -> type_name, 'quotehtml'), 'title' => filterInput($value -> title, 'quotehtml'), 'description' => filterInput($value -> description, 'quotehtml'), 'elements' => intval($value -> elements), 'id' => $value -> id, 'tooltip' => htmlspecialchars($value -> tooltip));
}

$template -> push('menu_categories', $c);
