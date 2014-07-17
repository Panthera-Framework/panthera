<?php
/**
  * Files upload, scanning, mime types recognition
  *
  * @package Panthera\modules\filesystem
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

/**
  * Upload categories management (wrapper for `upload_categories` table in DB)
  *
  * @package Panthera\modules\filesystem
  * @author Mateusz Warzyński
  */

class uploadCategory extends pantheraFetchDB
{
    protected $_tableName = 'upload_categories';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'name', 'array');
    protected $_viewPermission = 'upload.view.{$var}';
    protected $_viewPermissionColumn = 'name';

    /**
     * Returns category name
     *
     * @return string
     */

    public function getName()
    {
        if ($this -> title)
        {
            $t = @unserialize($this->title);

            if ($t !== False)
                return localize($t[0], $t[1]);

            return $this -> title;
        }

        return $this -> name;
    }

    /**
     * Filter for "maxfilesize" column
     * Will be executed on __set()
     *
     * @param mixed &$value Input to filter
     * @author Damian Kęska
     * @return null
     */

    public function maxfilesizeFilter(&$value)
    {
        if (!is_numeric($value))
            $value = filesystem::sizeToBytes($value);
    }

    /**
     * Filter for "mime_type" column
     *
     * @param mixed &$value Input to filter
     * @author Damian Kęska
     * @return null
     */

    public function mime_typeFilter(&$value)
    {
        $value = filesystem::validateMimes($value);
    }

    /**
     * Finds real max file size limit (includes PHP's upload_max_filesize, post_max_size and Panthera config key upload.maxsize)
     *
     * @param bool $humanReadable Return in human readable format
     * @return string|int
     */

    public function getMaxfilesize($humanReadable=False)
    {
        $sizes = array(
            $this -> panthera -> config -> getKey('upload.maxsize', 3145728, 'int', 'upload'),
            $this -> maxfilesize,
            intval(filesystem::sizeToBytes(ini_get('upload_max_filesize'))),
            intval(filesystem::sizeToBytes(ini_get('post_max_size'))),
        );

        $maxSize = min($sizes);

        if ($humanReadable)
            return filesystem::bytesToSize($maxSize);

        return intval($maxSize);
    }
}


/**
  * Uploaded file management (wrapper for `uploads` table in DB)
  *
  * @package Panthera\modules\filesystem
  * @author Damian Kęska
  */

class uploadedFile extends pantheraFetchDB
{
    protected $_tableName = 'uploads';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array', 'location');
    protected $__author = null;

    public function __set($key, $value)
    {
        if ($key == 'location' or $key == 'thumbnail')
            $value = pantheraUrl($value, True);

        parent::__set($key, $value);
    }
    
    /**
     * Check if user can access this resource
     *
     * @param pantheraUser $user User object
     * @author Damian Kęska
     */
    
    public function canRead($user='')
    {
        if (!$user)
            $user = panthera::getInstance() -> user;
            
        if (getUserRightAttribute($user, 'upload.deleteown') and ($user -> id == $this -> uploader_id))
            return True;
            
        // is this a public file?
        if (!$this -> __get('protected'))
            return True;
            
        return False;
    }
    
    /**
     * In this case it's just an alias to canRead()
     *
     * @param pantheraUser $user User object
     * @author Damian Kęska
     */
    
    public function canWrite($user='')
    {
        return $this -> canRead($user);
    }

    /**
      * Get file link (returns full, parsed link)
      *
      * @return string
      * @author Damian Kęska
      */

    public function getLink()
    {
        if (intval($this -> protected))
        {
            // install seo url if there is no any for downloads
            if (!$this -> panthera -> routing -> exists('download'))
            {
                $this -> panthera -> routing -> map('GET', 'file/[i:fileid]/[*:filename]', array(
                    'front' => 'download.php',
                ), 'download');
            }

            // generate seo url
            return $this -> panthera -> routing -> generate('download', array(
                'fileid' => $this -> id,
                'filename' => $this -> filename,
            ));
        }

        $url = $this -> panthera -> config -> getKey('url'); // this site url
        $location = pantheraUrl($this->__get('location'));

        return pantheraUrl($url.str_replace(SITE_DIR, '', $location));
    }

    /**
     * Get file name
     *
     * @return string
     */

    public function getName()
    {
        if (intval($this -> protected))
            return $this -> filename;

        return basename($this->getLink());
    }

    /**
      * Get thumbnail and create it if does not exists
      *
      * @param string|int $size Optional size eg. 100x100 or 200
      * @param bool $create Create thumbnail if does not exists (optional, False by default)
      * @param bool $leaveSmaller Leave smaller thumbnail if its too small to resize (optiona, True by default)
      *
      * @author Damian Kęska
      * @author Mateusz Warzyński
      * @return string Link to thumbnail
      */

    public function getThumbnail($size='', $create=False, $leaveSmaller=True)
    {
        $panthera = pantheraCore::getInstance();

        $fileType = filesystem::fileTypeByMime($this->__get('mime'));
        $fileInfo = pathinfo($this->__get('location'));

        if ($size)
        {
            $thumb = pantheraUrl('{$upload_dir}/_thumbnails/' .$size. 'px_' .$fileInfo['filename']. '.jpg');

            if(is_file($thumb))
                return $thumb;

            if(!is_file($thumb) and $fileType == 'image' and $create == True and class_exists('SimpleImage'))
            {
                $exp = explode('x', $size);

                $simpleImage = new SimpleImage();
                $simpleImage -> load(pantheraUrl($this->__get('location')));

                if (count($exp) > 1)
                    $simpleImage -> resize($exp[0], $exp[1]); // resize to WIDTHxHEIGHT
                else {
                    // resize smaller images
                    if ($simpleImage -> getWidth() <= $size)
                    {
                        if ($leaveSmaller == False)
                            $simpleImage -> resizeToWidth($size); // resize to width
                    } else {
                        // resize images bigger than $size
                        $simpleImage -> resizeToWidth($size);
                    }
                }

                $simpleImage -> save($thumb, 99, 0655);

                if(is_file($thumb))
                    return $thumb;
            }
        }

        if ($fileType == 'image')
            return str_replace('{$SITE_DIR}', '{$PANTHERA_URL}', $this->__get('location'));

        $mimesURL = pantheraUrl('{$PANTHERA_URL}/images/admin/mimes/');

        if (is_file(SITE_DIR. '/images/admin/mimes/' .$fileType. '.png'))
            return $mimesURL.$fileType. '.png';

        return $mimesURL. 'unknown.png';
    }

    /**
     * Get file save path
     *
     * @return string
     */

    public function getLocation()
    {
        return pantheraUrl($this->__get('location'));
    }

    /**
     * Get author object or name
     *
     * @param bool $name Return user name instead of object
     * @return string|pantheraUser
     */

    public function getAuthor($name=True)
    {
        if (!$this -> __author)
            $this -> __author = new pantheraUser('id', $this -> uploader_id);

        if ($name)
            return $this -> __author -> getName();

        return $this -> __author;
    }
}

/**
  * Upload functions
  *
  * @package Panthera\modules\filesystem
  *
  * @author Mateusz Warzyński
  * @author Damian Kęska
  */

class pantheraUpload
{
    protected static $uploadErrors = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded and/or no file selected to upload',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        101 => 'Invalid file type',
        102 => 'File reached accepted file size limit',
    );

    /**
     * Create new upload category
     *
     * @param string $name of category
     * @param int $id of author
     * @param array $mimeType of allowed files
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public static function createUploadCategory($name=null, $author_id='', $mimeType='', $title='', $maxFileSize=0, $skipUserCheck=False)
    {
        $panthera = pantheraCore::getInstance();

        if (!$skipUserCheck)
        {
            $user = new pantheraUser('id', $author_id);

            if (!$user->exists()) {
                $panthera -> logging -> output('Author ID is invalid, there is no user with this ID.', 'upload');
                return False;
            }
        }

        if (!$mimeType) {
            return False;
        }

        if (!is_numeric($maxFileSize))
            return False;

        if (!$name)
            $name = Tools::seoUrl($title). '-' .substr(md5(time().rand(999,9999)), 0, 4);

        return $panthera -> db -> insert('upload_categories', array(
            'name' => $name,
            'title' => $title,
            'author_id' => $author_id,
            'mime_type' => $mimeType,
            'maxfilesize' => $maxFileSize,
            'created' => DB_TIME_NOW,
            'modified' => DB_TIME_NOW,
            'protected' => 0,
        ));
    }

    /**
     * Delete upload category
     *
     * @param int $id of category
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public static function deleteUploadCategory($id)
    {
        $panthera = pantheraCore::getInstance();

        if ($panthera -> db -> query ('DELETE FROM `{$db_prefix}upload_categories` WHERE `id` = :id', array('id' => $id)))
            return True;

        return False;
    }

    /**
     * Pre-validate uploaded file to temporary directory before moving
     * Warning: returns int on fail and bool on success. Remember to compare the type eg. if (pantheraUpload::validate(...) !== True) { failed; }
     *
     * @param array $file Input file eg. $_FILES['file']
     * @param string $category (Optional) Check if uploaded file meets requirements of selected category
     * @param string|array $mimes Single mime as string or array of mimes (types also accepted @see filesystem::fileTypeByMime())
     * @param int $size (Optional) Max file size limit (if not set will be taken from category)
     * @author Damian Kęska
     * @return int|bool Returns int on error with error code, true if everything is fine
     */

    public static function validate($file, $category=null, $mimes=null, $size=null)
    {
        $panthera = pantheraCore::getInstance();

        if ($file['error'])
            return $file['error'];

        if (is_string($mimes))
            $mimes = array($mimes);
        elseif ($mimes === null)
            $mimes = array();

        // get mime types from category
        if ($category)
        {
            $obj = new uploadCategory('name', $category);

            if ($obj -> exists())
            {
                if ($obj -> mime_type and $obj -> mime_type != 'all')
                    $mimes = array_merge($mimes, explode(',', str_replace(' ', '', $obj -> mime_type)));

                if (intval($obj -> maxfilesize) !== 0 and filesize($file['tmp_name']) > intval($obj -> maxfilesize))
                    return 102; // file size limit reached
            }
        }

        if ($mimes)
        {
            $fileMime = filesystem::getFileMimeType($file['tmp_name']);
            $typeMime = filesystem::fileTypeByMime($fileMime); // allow expressions like "document", "audio", "video", "binary"

            $panthera -> logging -> output('Validating ' .$fileMime. '/' .$typeMime. ' mime, allowed: ' .json_encode($mimes), 'pantheraUpload');

            if (!in_array($fileMime, $mimes) and !in_array($typeMime, $mimes))
            {
                return 101; // mime mismatch code
            }
        }

        return true;
    }

    /**
     * Get upload error message (English)
     *
     * @param int $code Numeric code received from any validating function
     * @return string|null Returns english message
     */

    public static function getErrorMessage($code)
    {
        if (isset(self::$uploadErrors[$code]))
            return self::$uploadErrors[$code];
    }


    /**
     * Handle file upload
     *
     * @param $_FILE['input_name'], category name = 'default'
     *
     * @throws Exception
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */

    public static function handleUpload($file, $category, $uploaderID, $uploaderLogin, $protected, $public, $mime=null, $description='', $validated=true)
    {
        $panthera = pantheraCore::getInstance();

        if (!is_array($file))
            throw new Exception('$file must be array type');

        if (!$validated)
        {
            $validation = self::validate($file, $category, $mime);

            if ($validation !== true)
                throw new Exception(self::getErrorMessage($validation), $validation);
        }
        /*
        if (intval($file['error']))
        {
            if (isset(static::$uploadErrors[$file['error']]))
            {
                throw new Exception(static::$uploadErrors[$file['error']]);
            }
        }*/

        if ($file['size'] > $panthera -> config -> getKey('upload.maxsize', 3145728, 'int', 'upload'))
            return False;

        if (filesize($file['tmp_name']) > $panthera -> config -> getKey('upload.maxsize', 3145728, 'int', 'upload'))
        {
            $panthera -> logging -> output('Upload_max_size reached, rejecting file', 'pantheraUpload');
            return False;
        }

        if (!$mime)
            $mime = filesystem::getFileMimeType($file['tmp_name']);

        $name = $file['name'];
        $fileInfo = pathinfo($name);

        // cut out file name if too long
        if (strlen($fileInfo['filename']) > 30)
            $name = substr($fileInfo['filename'], 0, 30). '.' .$fileInfo['extension'];

        $uploadDir = SITE_DIR. '/' .$panthera -> config -> getKey('upload_dir'). '/' .$category;

        if (!is_dir($uploadDir))
        {
            mkdir($uploadDir);
            chmod($uploadDir, 0700);
        }

        // create directory for hidden files
        if (!is_dir($uploadDir. '/_private'))
        {
            mkdir($uploadDir. '/_private');
            chmod($uploadDir. '/_private', 0700);
        }

        // support for Apache's .htaccess
        if (!is_file($uploadDir. '/_private/.htaccess'))
        {
            $fp = fopen($uploadDir. '/_private/.htaccess', 'w');
            fwrite($fp, "deny from all\n");
            fclose($fp);
        }

        $originalName = basename($name);

        // put file to hidden directory protected by .htaccess and router
        if ($protected == True)
        {
            $uploadDir .= '/_private';
            $name = md5(PANTHERA_SEED.rand(999, 9999).time());
            $panthera -> logging -> output('File marked as protected, setting destination as "' .$uploadDir. '" for "' .$originalName. '"', 'pantheraUpload');
        }

        if (!is_writable($uploadDir))
            throw new Exception($uploadDir. ' is not a writable directory');

        // if file name already exists find new unique name
        if (is_file($uploadDir. '/' .$name))
        {
            $i= 0;

            while (True)
            {
                $i++;

                if (!is_file($uploadDir. '/' .$i. '_' .$name)) {
                    $name = $i. '_' .$name;
                    break;
                }
            }
        }

        $panthera -> logging -> output('Moving uploaded file from ' .$file['tmp_name']. ' to ' .$uploadDir. '/' .$name, 'pantheraUpload');
        rename($file['tmp_name'], $uploadDir. '/' .$name);
        chmod($uploadDir. '/' .$name, 0700);

        if (is_file($uploadDir. '/' .$name))
        {
            $values = array(
                'category' => $category,
                'location' => pantheraUrl($uploadDir. '/' .$name, True),
                'description' => $description,
                'mime' => $mime,
                'uploader_id' => $uploaderID,
                'uploader_login' => $uploaderLogin,
                'protected' => $protected,
                'public' => $public,
                'icon' => '',
                'filename' => $originalName,
                'created' => DB_TIME_NOW,
            );

            $type = filesystem::fileTypeByMime($mime);

            $panthera -> logging -> output('File type is "' .$type. '"', 'pantheraUpload');

            // try to create a thumbnail
            if ($type == 'image')
            {
                $dir = SITE_DIR. '/' .$panthera -> config -> getKey('upload_dir'). '/_thumbnails';
                $fileInfo = pathinfo($name);

                $panthera -> logging -> output('Attempting to create a thumbnail - ' .$dir. '/200px_' .$fileInfo['filename']. '.png', 'pantheraUpload');
                $simpleImage = new SimpleImage();
                $simpleImage -> load($uploadDir. '/' .$name);
                $simpleImage -> resizeToWidth(200); // resize to 100px width
                $simpleImage -> save($dir. '/200px_' .$fileInfo['filename']. '.png', IMAGETYPE_PNG, 85);
                chmod($dir. '/200px_' .$fileInfo['filename']. '.png', 0655);
            }

            return $panthera -> db -> insert('uploads', $values);

        } else {
            $panthera -> logging -> output('Cannot save file "' .$name. '", to directory "' .$uploadDir. '", details: ' .json_encode($file), 'pantheraUpload');
        }

        return False;
    }

    /**
      * Create a fake uploaded file
      *
      * @param string $formName
      * @param string $content Content encoded in base64 (without HTML data header)
      * @param string $fileName
      * @param string $type Mime type
     *
      * @author Damian Kęska
      * @return bool
      */

    public static function makeFakeUpload($formName, $content, $fileName, $type='text/plain')
    {
        $_FILES[$formName] = array('tmp_name' => '/tmp/' .md5($content), 'name' => $fileName, 'type' => $type, 'error' => 0, 'size' => strlen($content));
        $fp = fopen($_FILES[$formName]['tmp_name'], 'w');
        fwrite($fp, base64_decode($content));
        fclose($fp);
        return is_file($_FILES[$formName]['tmp_name']);
    }

    /**
      * Parse base64 uploaded file, decode header and return base64 encoded content
      *
      * @param string $data Data encoded in base64 with HTML data header
      * @param bool $decode Decode base64 content (optional)
      *
      * @author Damian Kęska
      * @return array of two elements - mime and content (encoded in base64)
      */

    public static function parseEncodedUpload($data, $decode=False)
    {
        $tmp = explode(';base64,', $data);
        $data = $tmp[1];

        if ($decode == True)
            $data = base64_decode($data);

        return array('mime' => str_ireplace('data:', '', $tmp[0]), 'content' => $data);
    }

     /**
      * Delete a file
      *
      * @package Panthera\Package
      * @param string $variable
      *
      * @author Damian Kęska
      */

    public static function deleteUpload($id, $location)
    {
        $panthera = pantheraCore::getInstance();

        if ($panthera -> db -> query ('DELETE FROM `{$db_prefix}uploads` WHERE `id` = :id', array('id' => $id)))
        {
            $location = pantheraUrl($location);
            $fileInfo = pathinfo($location);

            if (is_file($fileInfo['dirname']. '/../_thumbnails/200px_' .$fileInfo['filename']. '.' .$fileInfo['extension']))
                @unlink($fileInfo['dirname']. '/../_thumbnails/200px_' .$fileInfo['filename']. '.' .$fileInfo['extension']);

            $thumbs = glob($fileInfo['dirname']. '/../_thumbnails/*_' .$fileInfo['filename']. '.' .$fileInfo['extension']);

            foreach ($thumbs as $thumb)
            {
                if (is_file($thumb))
                    @unlink($thumb);
            }

            if (is_file($location))
                @unlink($location);

            if (!is_file($location))
                return True;
        }

        return False;
    }
}

/**
  * Filesystem and mimetype functions
  *
  * @package Panthera\modules\filesystem
  * @author Damian Kęska
  */

class filesystem
{
    /**
     * Recursive directories scanning
     *
     * @param string $dir Directory
     * @param bool $filesOnly Show only files?
     *
     * @author Damian Kęska
     * @return string
     */

    public static function scandirDeeply($dir, $filesOnly=True)
    {
        $files = scandir($dir);
        $list = array();

        if (!$filesOnly)
            $list[] = $dir;

        foreach ($files as $file)
        {
            if ($file == ".." or $file == ".")
                continue;

            if (is_link($dir. '/' .$file))
            {
                if (in_array(readlink($dir. '/' .$file), $list))
                    continue;
            }

            if (is_file($dir. '/' .$file) or is_link($dir. '/' .$file)) {
                $list[] = $dir. '/' .$file;

            } else {

                //if (!$filesOnly)
                //    $list[] = $dir. '/' .$file;

                $dirFiles = self::scandirDeeply($dir. '/' .$file, $filesOnly);

                foreach ($dirFiles as $dirFile)
                    $list[] = $dirFile;
            }

        }

        return $list;
    }

    /**
     * Convert size like "2M", "5 mb", "100 kilobytes" to bytes
     *
     * @param string $val Input string
     * @return int
     */

    public static function sizeToBytes($val)
    {
        $val = str_replace(',', '.', $val); // replace all commas to dots
        preg_match_all('/([0-9\.]+)/', $val, $_matches, PREG_SET_ORDER);

        if ($_matches)
        {
            $size = floatval($_matches[0][1]);
            $unit = str_replace($_matches[0][1], '', $val); // replace 0-9
            $unit = strtolower(trim($unit)); // remove whitespaces and make characters lowercase

            if ($unit == 'g' or $unit == 'gb' or $unit == 'gbytes' or $unit == 'gigabytes')
                $size *= 1024 * 1024 * 1024;
            elseif ($unit == 'm' or $unit == 'mb' or $unit == 'mbytes' or $unit == 'megabytes')
                $size *= 1024 * 1024;
            elseif ($unit == 'k' or $unit == 'kb' or $unit == 'kbytes' or $unit == 'kilobytes')
                $size *= 1024;
            elseif ($unit == 'b' or $unit == 'bytes' or $unit == 'byte') {
                // do nothing
            } else
                return false;

            return $size; // size in bytes
        }

        return false;
    }

    /**
     * Remove directory recursively
     *
     * @param string $dir Path
     * @see http://pl1.php.net/manual/en/function.rmdir.php
     *
     * @author erkethan@free.fr
     * @return bool
     */

    public static function deleteDirectory($dir)
    {
        if (!file_exists($dir))
            return true;

        if (!is_dir($dir) || is_link($dir))
            return unlink($dir);


        foreach (scandir($dir) as $item)
        {
                if ($item == '.' || $item == '..')
                    continue;

                if (!static::deleteDirectory($dir . "/" . $item))
                {
                    chmod($dir . "/" . $item, 0777);

                    if (!static::deleteDirectory($dir . "/" . $item))
                        return false;
                }
        }

        return rmdir($dir);
    }

    /**
     * Make a recursive copy of a directory
     *
     * @see http://stackoverflow.com/questions/9835492/move-all-files-and-folders-in-a-folder-to-another
     * @param string $src
     * @param string $dst
     *
     * @author Baba
     * @return void
     */

    public static function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir)))
        {
            if ($file != '.' and $file != '..')
            {
                if (is_dir($src . '/' . $file))
                    self::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                else
                    copy($src . '/' . $file,$dst . '/' . $file);
            }
        }

        closedir($dir);
    }

    /**
     * Get file basename
     *
     * @param string Path
     * @author Damian Kęska
     * @return string
     */

    public static function mb_basename($file)
    {
        $tmp = explode('/', strval($file));
        return end($tmp);
    }

    /**
     * Recognize mime type by file extension
     *
     * @param string $fileName, path to file
     * @author Mateusz Warzyński
     * @return string|bool If something will went wrong the application/octet-stream will be returned. False is returned when file is not readable or does not exists
     */

    public static function getFileMimeType($fileName)
    {
        if (!is_file($fileName) or !is_readable($fileName))
            return false;

        // use finfo to detect mime type
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $fileName);
        $mimetype = explode(';', $mimetype);

        // close finfo resource
        finfo_close($finfo);

        if (!$mimetype or !$mimetype[0])
            return 'application/octet-stream';

        return $mimetype[0];
    }

    /**
     * Colorize PHP code and return in table
     *
     * @param string $source_code PHP source code
     * @param int $start Line to start from
     * @param int $end Line to finish
     *
     * @author fsx.nr01@gmail.com
     * @author Damian Kęska
     * @return string
     */

    public static function printCode($source_code, $start=0, $end=0)
    {
        if (is_array($source_code))
            return false;

        $source_code = explode("\n", str_replace(array("\r\n", "\r"), "\n", $source_code));
        $line_count = 0;

        foreach ($source_code as $code_line)
        {
            $line_count++;

            if ($line_count <= $start and $start > -1)
                continue;

            if ($line_count > $end and $end != 0 and $end > $start)
                continue;

            $formatted_code .= '<tr><td>'.$line_count.'</td>';

            if (ereg('<\?(php)?[^[:graph:]]', $code_line))
                $formatted_code .= '<td>'. str_replace(array('<code>', '</code>'), '', highlight_string($code_line, true)).'</td></tr>';
            else
                $formatted_code .= '<td>'.ereg_replace('(&lt;\?php&nbsp;)+', '', str_replace(array('<code>', '</code>'), '', highlight_string('<?php '.$code_line, true))).'</td></tr>';
        }

        return '<table style="font: 1em Consolas, \'andale mono\', \'monotype.com\', \'lucida console\', monospace;">'.$formatted_code.'</table>';
    }

    /**
     * Get panthera file type classification by mime type
     *
     * @param string $mime Input mime type
     *
     * @author Damian Kęska
     * @return string binary, archive, document, script, audio, image, video. If not identified returns binary.
     */

    public static function fileTypeByMime($mime)
    {
        $mimes = array(
            'application/pdf' => 'document',
            'application/octet-stream' => 'binary',
            'application/x-gzip' => 'archive',
            'application/x-gtar' => 'archive',
            'application/x-tar' => 'archive',
            'application/zip' => 'archive',
            'application/x-compress' => 'archive',
            'application/x-compressed' => 'archive',
            'application/x-javascript' => 'script',
            'application/x-msaccess' => 'document',
            'application/msword' => 'document',
            'application/vnd.ms-powerpoint' => 'document',
            'application/x-latex' => 'document',
            'application/x-sh' => 'script',
            'text/html' => 'script',
            'text/css' => 'document',
        );

        if (isset($mimes[$mime]))
            return $mimes[$mime];

        $knownExp = array(
            'audio',
            'image',
            'video',
        );

        $exp = explode('/', $mime);

        if (in_array(strtolower($exp[0]), $knownExp))
            return strtolower($exp[0]);

        if ($exp[0] == 'application')
            return 'binary';
    }

    /**
     * Convert bytes to human readable format
     *
     * @param int $bytes Size in bytes to convert
     * @param int $precision Rounding precision
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return string
     */

    public static function bytesToSize($bytes, $precision = 2)
    {
        // size rate
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte))
            return $bytes . ' B';

        elseif (($bytes >= $kilobyte) && ($bytes < $megabyte))
            return round($bytes / $kilobyte, $precision) . ' KB';

        elseif (($bytes >= $megabyte) && ($bytes < $gigabyte))
            return round($bytes / $megabyte, $precision) . ' MB';

        elseif (($bytes >= $gigabyte) && ($bytes < $terabyte))
            return round($bytes / $gigabyte, $precision) . ' GB';

        elseif ($bytes >= $terabyte)
            return round($bytes / $terabyte, $precision) . ' TB';

        else
            return $bytes . ' B';
    }

    /**
     * Validates mime or group of comma separated mimes
     *
     * @param string|array $mimes Single mime or multiple mimes
     * @return string Comma separated mime types
     */

    public static function validateMimes($mimes)
    {
        if (!is_array($mimes))
            $mimes = explode(',', $mimes);

        $newArray = array();

        foreach ($mimes as $mime)
        {
            $mime = trim($mime);

            if (!preg_match('/^([A-Za-z0-9]+)\/?([A-Za-z0-9\_\-]+)?$/', $mime))
                continue;

            $newArray[] = $mime;
        }

        return implode(', ', $newArray);
    }
}

if (!function_exists('mime_content_type'))
{
    function mime_content_type($file) { return filesystem::getFileMimeType($file); }
}
