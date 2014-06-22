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
     * Do all jobs in one
     * 
     * @param mixed $data
     * @author Damian Kęska
     * @return null
     */

    public static function minifyAll($data='')
    {
        static::cleanUpOldTemplates($data);
        static::minifyHTML($data);
    }

    /**
     * Extract <script> tags from code and put into array
     * 
     * @param string $html Input HTML code
     * @author Damian Kęska
     * @return array
     */

    public static function extractScripts($html)
    {
        preg_match_all('#<script(.*?)</script>#is', $html, $matches);
        $scripts = array();
        
        if ($matches and count($matches[0]))
        {
            foreach ($matches[0] as $script)
            {
                $id = md5($script.rand(999,9999).time());
                $scripts[$id] = $script;
                
                $html = str_replace($script, '{$SCRIPT:' .$id. '}', $html);
            }
        }
        
        return array(
            'html' => $html, 
            'scripts' => $scripts,
        );
    }
    
    /**
     * Insert back scripts to HTML code
     * 
     * @param string $html Input HTML code stripped out of <script> tags
     * @param array $scripts Scripts extracted from HTML code
     * @author Damian Kęska
     * @return string
     */
    
    public static function insertScripts($html, $scripts)
    {
        foreach ($scripts as $id => $script)
            $html = str_replace('{$SCRIPT:' .$id. '}', $script, $html);
        
        return $html;
    }

    /**
     * Remove new lines \n from HTML code, but don't touch <script> tags
     * 
     * @param string $html Input HTML code
     * @return string
     */

    public static function cleanNewLines($html)
    {
        $html = str_replace("\n", " ", $html);
        $html = str_replace("\t", " ", $html);
        
        return $html;
    }

    /**
     * Reduce HTML template size by minification
     * 
     * @return null
     */

    public static function minifyHTML($data='')
    {
        $panthera = pantheraCore::getInstance();
        $dir = SITE_DIR. '/content/tmp/templates_c'; 
        $files = scandir($dir);

        $panthera -> logging -> output('Looking for Minify libraries', 'minifyJob');
        $html = getContentDir('share/minify/min/lib/Minify/HTML.php');
        $cssMin = getContentDir('share/minify/min/lib/CSSmin.php');
        $jsMinPlus = getContentDir('share/minify/min/lib/JSMinPlus.php');
        
        if (!$html)
            throw new Exception('Minify HTML library not found');
        
        include_once $html;
        
        if ($cssMin)
            include_once $cssMin;
        
        if ($jsMinPlus)
            include_once $jsMinPlus;
        
        $panthera -> logging -> output('Scaning template files', 'minifyJob');
        
        foreach ($files as $file)
        {
            if (!is_file($dir. '/' .$file))
                continue;
            
            $html = file_get_contents($dir. '/' .$file);
            $tmp = static::extractScripts($html);
            $html = $tmp['html'];
            
            $panthera -> logging -> output('Minifing HTML template ' .$file, 'minify');
            
            $html = Minify_HTML::minify($html, array(
                'jsCleanComments' => True,
            ));
            
            $html = self::cleanNewLines($html);
            
            if (function_exists('tidy_parse_string'))
            {
                $panthera -> logging -> output ('=> Running tidy on file', 'minify');
                $tidy = tidy_parse_string($html);
                $tidy -> cleanRepair();
                $html = tidy_get_output($tidy);
            }
            
            $html = static::insertScripts($html, $tmp['scripts']);
            
            $fp = fopen($dir. '/' .$file, 'w');
            fwrite($fp, $html);
            fclose($fp);
        }
    }
}
