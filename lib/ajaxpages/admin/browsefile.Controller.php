<?php
/**
  * Browse file
  *
  * @package Panthera\core\ajaxpages\browsefile
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
 * File content, page controller
 *
 * @package Panthera\core\ajaxpages\browsefile
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class browsefileAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.files.browse' => array(
            'Browse project files content - mostly code', 'filesystem',
        ),
    );

    protected $uiTitlebar = array('Browse file', 'filesystem');

    public static function getFilePath($path='')
    {
        $filePath = '';

        if (is_file(PANTHERA_DIR. '/' .$path) or is_file(SITE_DIR. '/' .$path))
        {
            if (is_file(PANTHERA_DIR . '/' . $path))
                $filePath = realpath(PANTHERA_DIR . '/' . $path);
            else
                $filePath = realpath(SITE_DIR . '/' . $path);
        }

        return $filePath;
    }

    /**
     * Display page with file content
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        // import libraries, locales
        $this -> panthera -> locale -> loadDomain('files');
        $this -> panthera -> importModule('filesystem');

        // create back button
        if (isset($_GET['back_btn']))
            $this -> panthera -> template -> push('back_btn', base64_decode($_GET['back_btn']));

        // get path to file
        $path = str_replace('../', '', str_ireplace(PANTHERA_DIR, '', str_ireplace(SITE_DIR, '', $_GET['path'])));
        $filePath = static::getFilePath($path);

        if (!strlen($filePath))  {
            $this -> panthera -> template -> push('err', localize('file not found'));
            return $this -> panthera -> template -> compile('browsefile.tpl');
        }

        $pathInfo = pathinfo($filePath);
        $allowedExtensions = array('php', 'inc', 'txt', 'js', 'tpl', 'po', 'jpg', 'png');

        // images and binary files we cant show
        if (!in_array($pathInfo['extension'], $allowedExtensions))  {
            $this -> panthera -> template -> push('err', localize('Cannot show binary files.', 'filesystem'));
            return $this -> panthera -> template -> compile('browsefile.tpl');
        }

        $mime = filesystem::getFileMimeType($filePath);
        $type = filesystem::fileTypeByMime($mime);

        // we cannot show config.php
        if (str_ireplace(SITE_DIR, '', $filePath) == '/content/app.php')  {
            $this -> panthera -> template -> push('err', localize('file not found'));
            return $this -> panthera -> template -> compile('browsefile.tpl');
        }

        // get file content
        if ($type == 'image') {
            $fileLines = '<img src="' . pantheraUrl('{$PANTHERA_URL}'.$path) . '" style="max-width:100%;">';
        } else {
            $fileContents = file_get_contents($filePath);
            $fileLines = filesystem::printCode($fileContents, intval($_GET['start']), intval($_GET['end']));
        }

        $owner = @posix_getpwuid(@fileowner($filePath));
        $group = posix_getgrgid(filegroup($filePath));

        $this -> panthera -> template -> push('file_path', $filePath);
        $this -> panthera -> template -> push('contents', $fileLines);
        $this -> panthera -> template -> push('perms', substr(sprintf('%o', fileperms($filePath)), -4));
        $this -> panthera -> template -> push('owner', $owner['name']);
        $this -> panthera -> template -> push('group', $group['name']);
        $this -> panthera -> template -> push('size_bytes', filesize($filePath));
        $this -> panthera -> template -> push('size', filesystem::bytesToSize(filesize($filePath)));
        $this -> panthera -> template -> push('modification_time', date($this -> panthera -> dateFormat, filemtime($filePath)));
        $this -> panthera -> template -> push('mime', $mime);
        $this -> panthera -> template -> push('type', $type);
        $this -> panthera -> template -> push('action', 'view');

        return $this -> panthera -> template -> compile('browsefile.tpl');
    }
}