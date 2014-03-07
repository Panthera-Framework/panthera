<?php
/**
  * Show list of ajax pages
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

class ajaxpagesAjaxControllerCore extends pageController
{
    protected $permissions = 'can_see_ajax_pages';
    
    protected $uiTitlebar = array(
        'Index of ajax pages', 'ajaxpages'
    );
    
    protected $files = array(
    
    );
    
    /**
     * List all controllers directories
     * 
     * @hook array ajaxpages.admin Directories listing
     * @return array
     */
    
    protected function scanDirectories()
    {
        return $this -> panthera -> get_filters('ajaxpages.admin', array_merge(
            filesystem::scandirDeeply(PANTHERA_DIR. '/ajaxpages/admin'), 
            filesystem::scandirDeeply(SITE_DIR. '/content/ajaxpages/admin'),
            filesystem::scandirDeeply(PANTHERA_DIR. '/pages'),
            filesystem::scandirDeeply(SITE_DIR. '/content/pages')
        ), True);
    }
    
    /**
     * Displays results (everything is here)
     * 
     * @hook array ajaxpages_list.raw Raw, unfiltered list of ajax pages
     * @hook array ajaxpages_list List of ajax pages
     * @return string
     */
    
    public function display()
    {
        $this -> panthera -> locale -> loadDomain('ajaxpages');
        $this -> panthera -> importModule('filesystem');
        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-tab-detach-icon.png', 'left');
        
        // scan both lib and content
        $this -> files = $this -> scanDirectories();
        
        // list of pages
        $this -> pages = array();
        
        $this -> pages[] = array(
            'location' => 'lib',
            'directory' => 'admin',
            'path' => PANTHERA_DIR. '/ajaxpages/admin/settings.php',
            'modtime' => date($this -> panthera -> dateFormat, filemtime(PANTHERA_DIR. '/ajaxpages/admin/settings.php')),
            'name' => 'system_info',
            'link' => '?display=settings&cat=admin&action=system_info'
        );
        
        $this -> pages[] = array(
            'location' => 'lib',
            'directory' => 'admin',
            'path' => PANTHERA_DIR. '/ajaxpages/admin/users.php',
            'modtime' => date($this -> panthera -> dateFormat, filemtime(PANTHERA_DIR. '/ajaxpages/admin/users.php')),
            'name' => 'my_account',
            'link' => '?display=users&cat=admin&action=my_account'
        );
        
        foreach ($this -> files as $file)
        {
            $pathinfo = pathinfo($file);
        
            if (strtolower($pathinfo['extension']) != 'php')
                continue;
        
            if (!is_file($file))
                continue;
        
            $location = 'unknown';
            
            // check if it's from /content or /lib
            if (strpos($file, SITE_DIR) !== False)
            {
                $location = 'content';
                $name = str_replace(SITE_DIR, '', str_ireplace('/content/ajaxpages/', '', $file));
            } elseif (strpos($file, PANTHERA_DIR) !== False) {
                $location = 'lib';
                $name = str_replace(PANTHERA_DIR, '', str_ireplace('/content/ajaxpages/', '', $file));
            }
            
            $directory = str_replace('/ajaxpages/', '', dirname($name));
            $name = str_ireplace('.php', '', basename($name));
        
            $this -> pages[] = array(
                'location' => $location,
                'directory' => $directory,
                'modtime' => date($this -> panthera -> dateFormat, filemtime($file)),
                'name' => str_replace('.Controller', '', $name),
                'path' => $file,
                'link' => '?display=' .str_replace('.Controller', '', $name),
                'objective' => (strpos($name, '.Controller') !== False),
            );
        }

        $this -> pages = $this -> panthera -> get_filters('ajaxpages_list.raw', $this -> pages, True);

        // ui.Searchbar integration
        $sBar = new uiSearchbar('uiTop');
        $sBar -> navigate(True);
        
        if ($sBar -> getQuery())
        {
            $this -> pages = $sBar -> filterData($this -> pages, $sBar -> getQuery());
        }
        
        $this -> pages = $this -> panthera -> get_filters('ajaxpages_list', $this -> pages, True);
        $this -> panthera -> template -> push('pages', $this -> pages);
        
        return $this -> panthera -> template -> compile('ajaxpages.tpl');
    }   
    
}
