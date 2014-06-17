<?php
class minifyJob
{
    /**
     * Clean up old template files
     * 
     * @return null
     */
    
    public static function cleanUpOldTemplates($data='')
    {
        $panthera = pantheraCore::getInstance();
        $dir = SITE_DIR. '/content/tmp/templates_c';
        
        $files = scandir($dir);
        $filesMtime = array();
        
        foreach ($files as $file)
        {
            if (!is_file($dir. '/' .$file))
                continue;
            
            $panthera -> logging -> output('[(' .date('G:i:s d.m.Y', filemtime($dir. '/' .$file)). ')] ==> Checking:' .$file, 'minify');
            
            $exp = explode('.', $file);
            $pos = (count($exp)-3);
            
            for ($i=$pos; $i < count($exp); $i++)
                unset($exp[$i]);
            
            $templateName = str_replace('.php', '', implode('.', $exp));
            $override = False;
            
            // clean up
            if (isset($filesMtime[$templateName]))
            {
                if (@filemtime($dir. '/' .$file) < $filesMtime[$templateName]['time'])
                { 
                    $override = True;   
                    unlink($dir. '/' .$file);
                    $panthera -> logging -> output('Removing old template file "' .$file. '"', 'minify');
                }
            }
            
            if (!isset($filesMtime[$templateName]) or $override)
            {
                $filesMtime[$templateName] = array(
                    'file' => $file,
                    'time' => @filemtime($dir. '/' .$file)
                );
            }
        }
    }

    /**
     * Reduce HTML template size by minification
     * 
     * @return null
     */

    public static function minifyHTML()
    {
        $panthera = pantheraCore::getInstance();
        $dir = SITE_DIR. '/content/tmp/templates_c'; 
        $files = scandir($dir);

        include_once getContentDir('share/minify/min/lib/Minify/HTML.php');
        include_once getContentDir('share/minify/min/lib/CSSmin.php');
        include_once getContentDir('share/minify/min/lib/JSMinPlus.php');
        
        foreach ($files as $file)
        {
            if (!is_file($dir. '/' .$file))
                continue;
            
            $panthera -> logging -> output('Minifing HTML template ' .$file, 'minify');
            $minified = Minify_HTML::minify(file_get_contents($dir. '/' .$file), array(
                'cssMinifier' => True,
                'jsMinifier' => True,
                'jsCleanComments' => True,
            ));
            
            $fp = fopen($dir. '/' .$file, 'w');
            fwrite($fp, $minified);
            fclose($fp);
        }
    }
}
