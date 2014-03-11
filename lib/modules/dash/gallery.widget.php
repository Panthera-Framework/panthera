<?php
/**
 * Gallery dash widget
 *
 * @package Panthera\core\modules\gallery
 * @license GNU Lesser General Public License 3, see license.txt
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

  
if (!defined('IN_PANTHERA'))
    exit;

/**
 * Gallery dash widget class
 * 
 * @package Panthera\core\modules\gallery
 */
  
class gallery_dashWidget extends pantheraClass
{
    /**
     * Main function that display widget
     * 
     * @return string
     */
    
    public function display()
    {
        $this -> panthera -> importModule('gallery');
        $this -> panthera -> template -> push ('galleryItems', gallery::getRecentPicture('', 15));
        return $this -> panthera -> template -> compile('dashWidgets/gallery.tpl');
    }
}
