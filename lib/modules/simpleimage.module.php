<?php
/**
  * Image processing - cropping, resizing, etc.
  *
  * @package Panthera\modules\image
  * @author Simon Jarvis
  * @copyright 2006 Simon Jarvis
  * @since 08/11/06
  * @see http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
  * @license GNU GPL
  */
 
class SimpleImage {
 
    public $image, $image_type;

    /**
      * Create image from string
      *
      * @param string $string Binary image data
      * @param $type of image (eg. IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG)
      * @return void 
      * @author Damian Kęska
      */
   
    public function loadFromString($string, $type=IMAGETYPE_JPEG)
    {
        $this->image = imagecreatefromstring($string);
        $this->image_type = $type;
    }
 
   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
    
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         $this->image = imagecreatefrompng($filename);
      }
   }
   
   public function save($filename, $compression=75, $permissions=null) {
        
      if( $this->image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image,$filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image);
      }
   }
   function getWidth() {
 
      return imagesx($this->image);
   }
   function getHeight() {
 
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
 
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
 
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
 
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
 
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }
   
   function cropBottom($px) {
      $width = $this -> getWidth();
      $height = $this -> getHeight();
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $width, $height-$px);
      $this->image = $new_image;
      unset($width); unset($height);
   }
}      
