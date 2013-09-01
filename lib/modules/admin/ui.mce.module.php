<?php
/**
  * Admin UI: WYSIWYG editor
  * 
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Admin UI: WYSIWYG editor
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */
  
class uiMce
{
    protected static $editors = null;
    protected static $configuration = null;

    /**
      * Get active editor name
      *
      * @return string 
      * @author Damian Kęska
      */

    public static function getActiveEditor()
    {
        global $panthera;
        return $panthera -> config -> getKey('mce.default', 'tinymce', 'string', 'mce');
    }
    
    /**
      * List all avaliable editors
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public static function getAvaliableEditors()
    {
        if ($editors)
            return self::$editors;
    
        $dirs = array(
            PANTHERA_DIR. '/modules/mce',
            SITE_DIR. '/content/modules/mce'
        );
        
        foreach ($dirs as $dir)
        {
            if (is_dir($dir))
            {
                $structure = scandir($dir);
                
                foreach ($structure as $file)
                {
                    if (!stripos($file, '.json'))
                    {
                        continue;
                    }
                    
                    self::$editors[] = str_replace('.json', '', $file);
                }
            }
        }
        
        return self::$editors;
    }
    
    /**
      * Get editor's configuration
      *
      * @param string name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function getConfiguration()
    {
        global $panthera;
        
        if (self::$configuration)
        {
            return self::$configuration;
        }
        
        $mceConfigDir = getContentDir('modules/mce/' .self::getActiveEditor(). '.json');
        
        if (!$mceConfigDir)
        {
            throw new Exception('Missing configuration for "' .self::getActiveEditor(). '" editor, looked in "modules/mce/' .getActiveEditor(). '.json"');
        }
        
        self::$configuration = json_decode(file_get_contents($mceConfigDir), true);
        
        foreach (self::$configuration['configuration'] as $key => $value)
        {
            self::$configuration['configuration'][$key]['value'] = $panthera -> config -> getKey('mce.' .self::getActiveEditor(). '.' .$key, $value['default'], $value['type'], 'mce');
        }

        return self::$configuration;
    }
    
    /**
      * Render a WYSIWYG editor and return output HTML
      *
      * @return mixed 
      * @author Damian Kęska
      */

    public static function display()
    {
        global $panthera;
        $mce = self::getConfiguration();
        $settings = array();
        
        foreach ($mce['configuration'] as $key => $value)
        {
            $settings[$key] = pantheraUrl($value['value']);
        }
        
        $settings['css'] = pantheraUrl($panthera -> config -> getKey('mce.css'));
        
        $panthera -> template -> push('mceSettings', $settings);
        return $panthera -> template -> display($mce['template'], True); // render template to variable and return it's contents
    }
}
