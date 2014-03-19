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
    protected $_constructBy = array('id', 'name');
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

    public function __set($key, $value)
    {
        if ($key == 'location' or $key == 'thumbnail')
            $value = pantheraUrl($value, True);

        parent::__set($key, $value);
    }
    
    /**
      * Get file link (returns full, parsed link)
      *
      * @return string 
      * @author Damian Kęska
      */

    public function getLink()
    {
        $url = $this -> panthera -> config -> getKey('url'); // this site url
        $location = pantheraUrl($this->__get('location'));

        return pantheraUrl($url.str_replace(SITE_DIR, '', $location));
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

        if ($size != '')
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
            return $this->__get('location');
            
        $mimesURL = pantheraUrl('{$PANTHERA_URL}/images/admin/mimes/');

        if (is_file(SITE_DIR. '/images/admin/mimes/' .$fileType. '.png'))
            return $mimesURL.$fileType. '.png';

        return $mimesURL. 'unknown.png';
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
     
    public static function createUploadCategory($name, $author_id, $mimeType)
    {
        $panthera = pantheraCore::getInstance();
        
        $user = new pantheraUser('id', $author_id);
        
        if (!$user->exists()) {
            $panthera -> logging -> output('Author ID is invalid, there is no user with this ID.', 'upload');
            return False;
        }
        
        if (!$mimeType) {
            return False;
        }
        
        if (strlen($name) < 2) {
            return False;
        }
        
        $values = array('name' => $name, 'author_id' => $author_id, 'mime_type' => $mimeType);
        
        $panthera -> db -> query('INSERT INTO `{$db_prefix}upload_categories` (`id`, `name`, `author_id`, `created`, `modified`, `mime_type`) VALUES (NULL, :name, :author_id, NOW(), NOW(), :mime_type);', $values);
        
        return $panthera -> db -> sql -> lastInsertId();   
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
      * Get upload categories from database
      *
      * @param mixed $by Array of values or nothing '' to just match all records
      * @param int $limit Query limit
      * @param int $limitFrom Offset to start from
      * @param string $orderBy Column to sort by
      * @param string $direction Sort ascending or descending
      *
      * @author Mateusz Warzyński
      * @return array 
      */

    public static function getUploadCategories($by='', $limit=0, $limitFrom=0, $orderBy='id', $direction='ASC')
    {
          $panthera = pantheraCore::getInstance();
          return $panthera->db->getRows('upload_categories', $by, $limit, $limitFrom, '', $orderBy, $direction);
    }


    /**
     * Handle file upload
     *
     * @param $_FILE['input_name'], category name = 'default'
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return string
     */

    public static function handleUpload($file, $category, $uploaderID, $uploaderLogin, $protected, $public, $mime='', $description='')
    {
        $panthera = pantheraCore::getInstance();

        if ($file['size'] > $panthera -> config -> getKey('upload_max_size'))
            return False;

        if (filesize($file['tmp_name']) > $panthera -> config -> getKey('upload_max_size'))
        {
            $panthera -> logging -> output('Upload_max_size reached, rejecting file', 'upload');
            return False;
        }

        if ($mime == '')
        {
            $mime = filesystem::getFileMimeType($file['name']);
        }
        
        $name = $file['name'];
        $fileInfo = pathinfo($name);

        // cut out file name if too long
        if (strlen($fileInfo['filename']) > 30)
            $name = substr($fileInfo['filename'], 0, 30). '.' .$fileInfo['extension'];

        if ($protected == True)
            $category = '_private';

        $uploadDir = SITE_DIR. '/' .$panthera -> config -> getKey('upload_dir'). '/' .$category;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);         
        }

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

        $panthera -> logging -> output('Moving uploaded file from ' .$file['tmp_name']. ' to ' .$uploadDir. '/' .$name, 'upload');
        rename($file['tmp_name'], $uploadDir. '/' .$name);
        chmod($uploadDir. '/' .$name, 0655);

        if (is_file($uploadDir. '/' .$name))
        {
            $values = array();
            $values['category'] = $category;
            $values['location'] = pantheraUrl($uploadDir. '/' .$name, True);
            $values['description'] = $description;
            $values['mime'] = $mime;
            $values['uploader_id'] = $uploaderID;
            $values['uploader_login'] = $uploaderLogin;
            $values['protected'] = $protected;
            $values['public'] = $public;
            $values['icon'] = '';
            //$values['icon'] = filesystem::fileTypeByMime($mime);

            $type = filesystem::fileTypeByMime($mime);

            $panthera -> logging -> output('upload.module::File type is "' .$type. '"');

            // try to create a thumbnail
            if ($type == 'image')
            {
                $dir = SITE_DIR. '/' .$panthera -> config -> getKey('upload_dir'). '/_thumbnails';
                $fileInfo = pathinfo($name);

                $panthera -> logging -> output('Attempting to create a thumbnail - ' .$dir. '/200px_' .$fileInfo['filename']. '.jpg', 'upload');
                $simpleImage = new SimpleImage();
                $simpleImage -> load($uploadDir. '/' .$name);
                $simpleImage -> resizeToWidth(200); // resize to 100px width
                $simpleImage -> save($dir. '/200px_' .$fileInfo['filename']. '.jpg', IMAGETYPE_JPEG, 85);        
                chmod($dir. '/200px_' .$fileInfo['filename']. '.jpg', 0655);
            }

            $panthera -> db -> query('INSERT INTO `{$db_prefix}uploads` (`id`, `category`, `location`, `description`, `icon`, `mime`, `uploader_id`, `uploader_login`, `protected`, `public`) VALUES (NULL, :category, :location, :description, :icon, :mime, :uploader_id, :uploader_login, :protected, :public);', $values);
            
            return $panthera -> db -> sql -> lastInsertId();
            
        } else
            $panthera -> logging -> output('upload.module::Cannot save file "' .$name. '", directory "' .$uploadDir. '" is not writable');

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
      * Search for uploaded files in database
      *
      * @param mixed $by Array of values or nothing '' to just match all records
      * @param int $limit Query limit
      * @param int $limitFrom Offset to start from
      * @param string $orderBy Column to sort by
      * @param string $inc Sort ascending or descending
      *
      * @author Damian Kęska
      * @return array 
      */

    public static function getUploadedFiles($by='', $limit=0, $limitFrom=0, $orderBy='id', $inc='DESC')
    {
          $panthera = pantheraCore::getInstance();
          return $panthera -> db -> getRows('uploads', $by, $limit, $limitFrom, 'uploadedFile', $orderBy, $inc);
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

            @unlink($fileInfo['dirname']. '/../_thumbnails/200px_' .$fileInfo['filename']. '.' .$fileInfo['extension']);
            
            $thumbs = glob($fileInfo['dirname']. '/../_thumbnails/*_' .$fileInfo['filename']. '.' .$fileInfo['extension']);
            
            foreach ($thumbs as $thumb)
                @unlink($thumb);
            
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
     * @param string (directory), bool (show only files?)
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
                    
                if (!deleteDirectory($dir . "/" . $item)) 
                { 
                    chmod($dir . "/" . $item, 0777); 
                    
                    if (!self::deleteDirectory($dir . "/" . $item)) 
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
     * 
     * @author Damian Kęska
     * @return string
     */

    public static function mb_basename($file) 
    { 
        return end(explode('/',$file)); 
    } 
    
    /**
     * Recognize mime type by file extension
     *
     * @param string $fileName, path to file
     * 
     * @author Mateusz Warzyński
     * @return string
     */

    public static function getFileMimeType($fileName)
    {
        // use finfo to detect mime type
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $fileName);
        
        // close finfo resource 
        finfo_close($finfo);
        
        return $mimetype;
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
     * @return string
     */

    public static function fileTypeByMime($mime)
    {
        $mimes = array();
        $mimes['application/pdf'] = 'pdf';
        $mimes['application/octet-stream'] = 'binary';
        $mimes['application/x-gzip'] = 'archive';
        $mimes['application/x-gtar'] = 'archive';
        $mimes['application/x-tar'] = 'archive';
        $mimes['application/zip'] = 'archive';
        $mimes['application/x-compress'] = 'archive';
        $mimes['application/x-compressed'] = 'archive';
        $mimes['application/x-javascript'] = 'script';
        $mimes['application/x-msaccess'] = 'document';
        $mimes['application/msword'] = 'word';
        $mimes['application/vnd.ms-powerpoint'] = 'powerpoint';
        $mimes['application/x-latex'] = 'document';
        $mimes['application/x-sh'] = 'script';
        $mimes['text/html'] = 'script';
        $mimes['text/css'] = 'document';

        if (array_key_exists($mime, $mimes))
            return $mimes[$mime];

        $knownExp = array('audio', 'image', 'video');

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
}

if (!function_exists('mime_content_type'))
{
    function mime_content_type($file) { return filesystem::getFileMimeType($file); }
}
