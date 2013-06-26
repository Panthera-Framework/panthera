<?php
/**
  * Gallery - simple gallery management module
  * 
  * @package Panthera\modules\gallery
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
global $panthera;
$panthera -> addPermission('can_view_galleryItem', localize('Can view gallery items', 'messages'));
$panthera -> addPermission('can_edit_galleryItem', localize('Can edit gallery items', 'messages'));

class galleryItem extends pantheraFetchDB
{
    protected $_tableName = 'gallery_items';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'url_id', 'link', 'array');

    public function __get($var)
    {
        return pantheraUrl(parent::__get($var));
    }

    public function __set($var, $value)
    {
        return parent::__set($var, pantheraUrl($value, True));
    }

    public function getThumbnail($size='', $create=False)
    {
        $this -> panthera -> importModule('simpleImage');
        $this -> panthera -> importModule('filesystem');


        $link = pantheraUrl($this->__get('link'));
        $filePath = pantheraUrl(str_replace('{$PANTHERA_URL}/', '', pantheraUrl($link, True)));
        $mime = getFileMimeType($filePath);

        // TODO: handling external links (saving to tmp and generating thumbnails)
        if (substr($filePath, 0, 7) == 'http://')
            return False;

        $fileType = fileTypeByMime($mime);
        $fileInfo = pathinfo($filePath);

        if ($size != '')
        {
            $thumb = pantheraUrl('{$upload_dir}/_thumbnails/' .$size. 'px_' .$fileInfo['filename']. '.jpg');

            if(is_file($thumb))
                return $thumb;

            if(!is_file($thumb) and $fileType == 'image' and $create == True)
            {
                $exp = explode('x', $size);

                $simpleImage = new SimpleImage();
                $simpleImage -> load(pantheraUrl($filePath));

                if (count($exp) > 1)
                    $simpleImage -> resize($exp[0], $exp[1]); // resize to WIDTHxHEIGHT
                else
                    $simpleImage -> resizeToWidth($size); // resize to width

                $simpleImage -> save($thumb, IMAGETYPE_JPEG, 85);

                if(is_file($thumb))
                    return $thumb;
            }
        }

        if (is_file(PANTHERA_DIR. '/images/mimes/' .$fileType. '.png'))
            return $url. '/images/mimes/' .$fileType. '.png';

        return $url. '/images/mimes/unknown.png';
    }
}

class galleryCategory extends pantheraFetchDB
{
    protected $_tableName = 'gallery_categories';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
}

class gallery
{
    /**
      * Get most recent picture from gallery
      *
      * @param int $gallery_id Gallery id (optional)
      * @param int $count Count
      * @return object
      * @author Damian Kęska
      */

    public static function getRecentPicture($gallery_id='', $count=1)
    {
        global $panthera;

        $count = intval($count);

        if ($gallery_id != '')
            $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}gallery_items` WHERE `gallery_id` = :gallery_id ORDER BY `created` DESC LIMIT 0,:count', array('gallery_id' => $gallery_id, 'count' => $count));
        else
            $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}gallery_items` ORDER BY `created` DESC LIMIT 0,:count', array('count' => $count));


        if ($count == 1)
        {
            $array = $SQL -> fetch();
            return new galleryItem('array', $array);

        } elseif ($count > 1) {
            $array = $SQL -> fetchAll();
            $oArray = array();

            foreach ($array as $item)
            {
                $oArray[] = new galleryItem('array', $item);
            }

            return $oArray;
        }
    }
}

function getGalleryItems($by, $limit=0, $limitFrom=0)
{
      global $panthera;
      return $panthera->db->getRows('gallery_items', $by, $limit, $limitFrom, 'galleryItem', 'id', 'DESC');
}

function getGalleryCategories($by, $limit=0, $limitFrom=0)
{
      global $panthera;
      return $panthera->db->getRows('gallery_categories', $by, $limit, $limitFrom, 'galleryCategory', 'id', 'DESC');
}

function removeGalleryItem($id)
{
    global $panthera;
    $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}gallery_items` WHERE `id` = :id', array('id' => $id));
    return (bool)$SQL->rowCount();
}

function removeGalleryCategory($id)
{
    global $panthera;
    $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}gallery_categories` WHERE `id` = :id', array('id' => $id));
    return (bool)$SQL->rowCount();
}

function createGalleryCategory($title, $login, $user_id, $language, $visibility, $user_full_name)
{
    global $panthera;
    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}gallery_categories` (`id`, `title`, `author_login`, `author_id`, `language`, `created`, `modified`, `visibility`, `author_full_name`, `thumb_id`, `thumb_url`) VALUES (NULL, :title, :author_login, :author_id, :language, NOW(), NOW(), :visibility, :author_full_name, "", "");', array('title' => $title, 'author_login' => $login, 'language' => $language, 'author_id' => $user_id, 'visibility' => $visibility, 'author_full_name' => $user_full_name));
    return (bool)$SQL->rowCount();
}

function createGalleryItem($title, $description, $link, $gallery_id, $visibility, $upload)
{
    global $panthera;

    $thumbnail = '';

    $panthera -> importModule('simpleImage');
    $fileInfo = pathinfo($link);
    $size = intval($panthera -> config -> getKey('gallery_thumbs_width', 240, 'int'));

    if ($size < 5)
        $size = 240;

    $thumb = pantheraUrl('{$upload_dir}/_thumbnails/' .$size. '_' .$fileInfo['filename']. '.jpg');

    if (!is_file($thumb))
    {
        $panthera -> logging -> output('createGalleryItem::Creating thumbnail for file "' .pantheraUrl($upload->location). '", width=' .$size);
        $simpleImage = new simpleImage();
        $simpleImage -> load(pantheraUrl($upload->location));
        $simpleImage -> resizeToWidth($size);
        $simpleImage -> save(PANTHERA_DIR. '/' .$thumb, IMAGETYPE_JPEG, 85);
        $panthera -> logging -> output('createGalleryItem::Saving thumbnail to file "' .PANTHERA_DIR. '/' .$thumb. '"');
    }

    $thumbnail = $panthera->config->getKey('url'). '/' .$thumb;

    $url_id = seoUrl(rand(99, 9999). '-' .$title);

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}gallery_items` (`id`, `title`, `description`, `created`, `url_id`, `link`, `thumbnail`, `gallery_id`, `visibility`, `upload_id`) VALUES (NULL, :title, :description, NOW(), :url_id, :link, :thumbnail, :gallery_id, :visibility, :upload_id);', array('title' => $title, 'description' => $description, 'url_id' => $url_id, 'link' => $link, 'thumbnail' => $thumbnail, 'gallery_id' => $gallery_id, 'visibility' => $visibility, 'upload_id' => $upload->id));
    return (bool)$SQL->rowCount();
}
