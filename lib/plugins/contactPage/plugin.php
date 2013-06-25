<?php
/**
  * Simple contact page with Google Maps support
  * @package Panthera\plugins\contactPage
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

// register plugin
$pluginInfo = array('name' => 'Contact page', 'author' => 'Damian Kęska', 'description' => 'Simple contact page', 'version' => PANTHERA_VERSION);
$panthera -> addPermission('can_edit_contact', localize('Can edit contact page (admin panel)', 'messages'));

function displayContactPage()
{
    global $template, $panthera, $user;

    if ($_GET['display'] == 'contact')
    {
        $panthera -> locale -> loadDomain('contactpage');

        $language = $panthera -> locale -> getActive();

        if (!getUserRightAttribute($user, 'can_edit_contact'))
        {
            $template->display('no_access.tpl');
            pa_exit();
        }

        if (@$_GET['action'] == 'save')
        {
            $mdata = $_POST['map_bounds'];
            $email_first = $_POST['email_first'];
            $email_second = $_POST['email_second'];
            $email_third = $_POST['email_third'];
            $contact_text = $_POST['adress_text'];

            if(strlen($contact_text) > 0)
                $panthera->config->setKey('contact_text_' .$language, $contact_text, 'string');

            if(isJson($mdata))
                $panthera->config->setKey('map_data_' .$language, $mdata);
            else {
                print(json_encode(array('status' => 'failed', 'error ' => localize('Invalid map location data'))));
            }

            if (strlen($email_first) > 0)
            {
                if (filter_var($email_first, FILTER_VALIDATE_EMAIL))
                    $panthera->config->setKey('contact_email_first_' .$language, $email_first, 'email');
                else {
                    print(json_encode(array('status' => 'failed', 'error ' => localize('Invalid e-mail adress in first field'))));
                    pa_exit();
                }
            }

            if (strlen($email_second) > 0)
            {
                if (filter_var($email_second, FILTER_VALIDATE_EMAIL))
                    $panthera->config->setKey('contact_email_second_' .$language, $email_second, 'email');
                else {
                    print(json_encode(array('status' => 'failed', 'error ' => localize('Invalid e-mail adress in second field'))));
                    pa_exit();
                }
            }

            if (strlen($email_third) > 0)
            {
                if (filter_var($email_third, FILTER_VALIDATE_EMAIL))
                    $panthera->config->setKey('contact_email_third_' .$language, $email_third, 'email');
                else {
                    print(json_encode(array('status' => 'failed', 'error ' => localize('Invalid e-mail adress in third field'))));
                    pa_exit();
                }
            }

            print(json_encode(array('status' => 'success', 'message' => localize('Contact has been successfully saved!'))));
            pa_exit();
        }


        $map_data = json_decode(stripslashes($panthera->config->getKey('map_data_' .$language, '{"bounds":{"Z":{"b":50.52538601346569,"d":50.78657485494268},"fa":{"b":17.58736379609377,"d":18.27400930390627}},"zoom":10,"center":{"jb":50.65616198748283,"kb":17.93068655000002}}')));

        $html = str_replace("\n", '\\n', $panthera->config->getKey('contact_text_' .$language));
        $html = str_replace("\r", '\\r', $html);
        $html = htmlspecialchars($html, ENT_QUOTES);

        $template -> push ('map_zoom', $map_data->zoom);
        $template -> push ('map_x', $map_data->center->jb);
        $template -> push ('map_y', $map_data->center->kb);
        $template -> push ('email_first', $panthera->config->getKey('contact_email_first_' .$language));
        $template -> push ('email_second', $panthera->config->getKey('contact_email_second_' .$language));
        $template -> push ('email_third', $panthera->config->getKey('contact_email_third_' .$language));
        $template -> push ('adress_text', $html);

        $template -> display('contact.tpl');
        pa_exit();
    }
}

function contactToAjaxList($list)
{
    $list[] = array('location' => 'plugins', 'name' => 'contact', 'link' => '?display=contact');

    return $list;
}

function contactToAdminMenu($menu) { $menu -> add('contact', localize('Contact'), '?display=contact', '', '', ''); }
$panthera -> add_option('admin_menu', 'contactToAdminMenu');

function contactPageToDash($attr) {
    if ($attr[1] != "main") { return $attr; }
    $attr[0][] = array('link' => '?display=contact', 'name' => localize('Contact'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/contact.png', 'linkType' => 'ajax');
    return $attr;
}

$panthera -> add_option('dash_menu', 'contactPageToDash');

$panthera -> add_option('ajaxpages_list', 'contactToAjaxList');
$panthera -> add_option('ajax_page', 'displayContactPage');
