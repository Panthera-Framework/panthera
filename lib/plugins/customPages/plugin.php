<?php
/**
    * @package Panthera
    * @subpackage core
    * @copyright (C) Damian Kęska, Mateusz Warzyński
    * @license GNU Affero General Public License 3, see license.txt
    */

// register plugin
$pluginInfo = array('name' => 'Custom Pages', 'author' => 'Damian Kęska, Mateusz Warzyński', 'version' => PANTHERA_VERSION, 'description' => 'Static pages management');

$panthera -> addPermission('can_customPages', localize('Can view custom pages (admin panel)', 'messages'));
$panthera -> addPermission('can_edit_customPages', localize('Can edit custom pages (admin panel)', 'messages'));

// context can be added only at admin panel or when plugin starts at first time
//$panthera -> append_context('customPages', '{$root}/index.php'); // enable plugin only in /index.php
//$panthera -> append_context('customPages', '{$root}/_ajax.php'); // enable plugin only in /index.php

class customPage extends pantheraFetchDB
{
    protected $_tableName = 'custom_pages';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'url_id', 'unique', 'array');

    public function __get($var)
    {
        if ($var == 'html')
            return pantheraUrl(parent::__get($var));

        return parent::__get($var);
    }

    public function __set($var, $value)
    {
        if ($var == 'html')
            return parent::__set($var, pantheraUrl($value, True));

       return parent::__set($var, $value);

    }

    /*public function save()
    {
        if ($this->_dataModified == True)
        {
            $id = (integer)$this->_data['id'];
            $this->panthera->logging->output('plugin::customPages::Saving customPage with id ' .$id);

            // we cant use id, so we have to remove it (id cant be changed because its used in WHERE clause)
            $copied = $this->_data;
            unset($copied['id']);
            unset($copied['mod_time']);

            // $set[0] will be a query string like `id` = :id, `name` = :name and $set[1] will be values array('id' => 1, 'name' => 'Damien')
            $set = $this->panthera->db->dbSet($copied);
            $set[1]['id'] = $id;

            #var_dump('UPDATE `{$db_prefix}custom_pages` SET ' .$set[0]. ' WHERE `id` = :id;');
            #var_dump($set[1]);

            $SQL = $this->panthera->db->query('UPDATE `{$db_prefix}custom_pages` SET ' .$set[0]. ', `mod_time` = NOW() WHERE `id` = :id;', $set[1]);

            // data was already saved, so we are returning to previous state
            $this->_dataModified = False;
        }
    }*/
}

/**
 * Get all custom pages from `{$db_prefix}_custom_pages` matching criteries specified in parameters
 *
 * @return array
 * @author Mateusz Warzyński
 */

function getCustomPages($by, $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
{
      global $panthera;
      return $panthera->db->getRows('custom_pages', $by, $limit, $limitFrom, 'customPage', $orderBy, $order);
}

/**
 * Create custom page
 *
 * @return bool
 * @author Mateusz Warzyński
 */

 // $meta_tags, $html,
function createCustomPage($title, $language, $author_name, $author_id, $unique, $url_id)
{
    global $panthera;
    $array = array('unique' => $unique, 'url_id' => $url_id, 'title' => $title, 'meta_tags' => '', 'html' => '', 'author_name' => $author_name, 'author_id' => $author_id, 'language' => $language, 'mod_author_name' => $author_name, 'mod_author_id' => $author_id);

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}custom_pages` (`id`, `unique`, `url_id`, `title`, `meta_tags`, `html`, `author_name`, `author_id`, `language`, `created`, `mod_author_name`, `mod_author_id`, `mod_time`) VALUES (NULL, :unique, :url_id, :title, :meta_tags, :html, :author_name, :author_id, :language, NOW(), :mod_author_name, :mod_author_id, NOW());', $array);

    if ($SQL)
      return True;

    return False;
}


/**
 * Simply remove custom page by `id`. Returns True if any row was affected
 *
 * @return bool
 * @author Mateusz Warzyński
 */

function removeCustomPage($id)
{
    global $panthera;
    $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}custom_pages` WHERE `id` = :id', array('id' => $id));

    if ($SQL)
        return True;

    return False;
}


function customPagesDisplay()
{
    global $template;
    $template -> display();
}

if($_SERVER['SCRIPT_NAME'] == PANTHERA_WEBROOT. '/index.php')
{
    if (isset($_GET['cpage']))
    {
        $id = $_GET['cpage'];
        $cpage = new customPage('url_id', $id);

        if ($cpage -> exists())
        {
            $template -> push ('page_title', $cpage->title);
            $template -> push ('content', $cpage->html);
            $template -> push ('author_name', $cpage->author_name);
            $template -> push ('author_id', $cpage->author_id);
            $template -> push ('page_language', $cpage->language);
            $template -> push ('meta_tags', parseMetaTags(@unserialize($cpage->meta_tags)));

            // dont load index.php content, just quite right after we load plugins
            $panthera -> quitAfterPlugins = True;
            $panthera -> add_option('quitAfterPlugins', 'customPagesDisplay'); // display the page after quit
        }
    }
}

function customPagesAjax()
{
    global $panthera, $user, $template;

    if (@$_GET['display'] == 'custom')
    {
         if (!getUserRightAttribute($user, 'can_customPages') and !getUserRightAttribute($user, 'can_manage_custompage_'.$id))
         {
              $template->display('no_access.tpl');
              pa_exit();
         }

         $tpl = 'custompages.tpl';

         $panthera -> locale -> loadDomain('custompages');

         if (@$_GET['action'] == "edit_page")
         {
              $tpl = 'custompages_editpage.tpl';

              $pid = @$_GET['pid'];
              $uid = $_GET['uid'];

              $template -> push('page_id', $pid);

              if ($pid != null)
                  $cpage = new customPage('id', $pid);
              else
                  $cpage = new customPage('url_id', $uid);

              if (!$cpage -> exists())
              {
                  $tpl = 'no_page.tpl';
              } else {
                  if (@$_GET['subaction'] == 'post_form')
                  {
                      if (!getUserRightAttribute($user, 'can_edit_customPages') and !getUserRightAttribute($user, 'can_manage_custompage_'.$id))
                          ajax_exit(array('status' => 'failed', 'message' => localize('You dont have rights to edit this page', 'messages')));

                      if (isset($_POST['content_title_'.$id]))
                      {
                          $title = htmlspecialchars($_POST['content_title_'.$id]);

                          if (strlen($title) > 0)
                          {
                              $cpage -> title = $title;
                          }
                      }

                      if (strlen($_POST['page_content_custom_'.$pid]) < 10)
                          ajax_exit(array('status' => 'failed', 'message' => localize('Message is too short')));

                      // last time modified by user...
                      $cpage -> mod_author_name = $user->login;
                      $cpage -> mod_author_id = $user->id;
                      $cpage -> html = $_POST['page_content_custom_'.$pid];
                      $cpage -> url_id = seoUrl($cpage->title);

                      $i = 0;
                      $iMax = 15; // max of meta_tags allowed

                      foreach ($_POST as $Key => $Value)
                      {
                          if(substr($Key, 0, 4) == "tag_")
                          {
                              $i++;
                              $Value = filterMetaTag($Value);

                              if($Value == "")
                                  continue;

                              if ($i == $iMax)
                                  break;

                              $tags[] = $Value;
                          }
                      }

                      $cpage -> meta_tags = serialize($tags);
                      $cpage -> save();

                      ajax_exit(array('status' => 'success', 'message' => localize('Page has been successfully edited!')));
                  }


                  if (@$_GET['section'] == 'tags')
                  {
                      if (!getUserRightAttribute($user, 'can_edit_customPages') and !getUserRightAttribute($user, 'can_manage_custompage_'.$id))
                          ajax_exit(array('status' => 'failed', 'message' => localize('You dont have rights to edit this page', 'messages')));

                      $tags = @unserialize($cpage->meta_tags);
                      print(json_encode(array('tags' => $tags)));
                      pa_exit();
                  } else {
                      $html = str_replace("\n", '\\n', $cpage->html);
                      $html = str_replace("\r", '\\r', $html);
                      $html = htmlspecialchars($html, ENT_QUOTES);


                      $template -> push('custompage_title', $cpage->title);
                      $template -> push('custompage_title_escaped', addslashes($cpage->title));
                      $template -> push('custompage_url_id', $cpage->url_id);
                      $template -> push('custompage_unique', $cpage->unique);
                      $template -> push('custompage_language', $cpage->language);
                      $template -> push('custompage_id', $cpage->id);
                      $template -> push('custompage_author_name', $cpage->author_name);
                      $template -> push('custompage_author_id', $cpage->author_id);
                      $template -> push('custompage_created', $cpage->created);
                      $template -> push('custompage_modified', $cpage->mod_time);
                      $template -> push('custompage_mod_author', $cpage->mod_author_name);
                      $template -> push('custompage_mod_author_id', $cpage->mod_author_id);
                      $template -> push('custompage_html', $html);
                      $template -> push('tag_list', @unserialize($cpage->meta_tags));
                      $template -> push('action', 'edit_page');

                      if ($cpage -> admin_tpl != '')
                        $tpl = $cpage -> admin_tpl;
                  }
              }

              $template -> display($tpl);
              pa_exit();
          } elseif (@$_GET['action'] == "add_page")
          {
              if (createCustomPage($_POST['title'], $_POST['language'], $user -> login, $user -> id, md5(rand(666, 6666)), seoUrl($_POST['title'])))
                        ajax_exit(array('status' => 'success', 'message' => localize('Page has been successfuly added!')));
              else
                        ajax_exit(array('status' => 'error', 'message' => localize('Error! Cannot add custom page!')));

          } elseif (@$_GET['action'] == "delete_page") {
              $pid = $_GET['pid'];
              $cpage = new customPage('id', $pid);
              if ($cpage->exists())
              {
                  if (removeCustomPage($cpage -> id))
                        ajax_exit(array('status' => 'success'));
                  else
                        ajax_exit(array('status' => 'error'));
              } else {
                  ajax_exit(array('status' => 'error'));
              }
          } else {

              if (isset($_GET['lang'])) {
                  if ($_GET['lang'] == 'all')
                        $p = getCustomPages();
                  else
                        $p = getCustomPages(array('language' => $_GET['lang']));
                  $template -> push('current_lang', $_GET['lang']);
              } else {
                  $p = getCustomPages(array('language' => $panthera -> locale -> getActive()));
              }

              if (count($p) > 0)
              {
                    foreach ($p as $page)
                    {
                          $array[] = array('id' => $page->id, 'unique' => $page->unique, 'url_id' => $page->url_id, 'modified' => $page->mod_time, 'created' => $page->created, 'title' => $page->title, 'author_name' => $page->author_name, 'mod_author_name' => $page->mod_author_name, 'language' => $page->language);
                    }
                    $template->push('pages_list', $array);
              }

              $template -> push('locales', $panthera -> locale -> getLocales());
              $template -> display($tpl);
              pa_exit();
          }
    }
}

function customPageToList($list)
{
    $list[] = array('location' => 'plugins', 'name' => 'customPages', 'link' => '?display=custom');

    return $list;
}

function customToAdminMenu($menu) { $menu -> add('custom', localize('Custom pages'), '?display=custom', '', '', ''); }
$panthera -> add_option('admin_menu', 'customToAdminMenu');

function customToDash($attr) {
    if ($attr[1] != "main") { return $attr; }
    $attr[0][] = array('link' => '?display=custom', 'name' => localize('Custom pages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/custom-pages.png', 'linkType' => 'ajax');
    return $attr;
}

$panthera -> add_option('dash_menu', 'customToDash');

$panthera -> add_option('ajaxpages_list', 'customPageToList');
$panthera -> add_option('ajax_page', 'customPagesAjax');

// example usage

#$t -> meta_tags = 'testa';
#var_dump($t -> title);
#$t -> save();
?>
