<?php
/**
  * Templates management functions
  *
  * @package Panthera\modules\libtemplate
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Static functions for templates management
  *
  * @package Panthera\modules\libtemplate
  * @author Damian Kęska
  */

class libtemplate
{
    /** CSS, JS, Images and other files cache **/

    /**
      * Find all files to update from templates and copy them
      *
      * @return array
      * @author Damian Kęska
      */

    public static function webrootMerge($customTemplates=array())
    {
        $panthera = pantheraCore::getInstance();

        $mainTemplate = $panthera -> config -> getKey('template');

        // example data: array('admin' => True) so the /lib will be merged or array('admin' => False) for /content only merging
        $configTemplates = $panthera -> config -> getKey('webroot.templates', array(), 'array', 'webroot');

        // example data: array('admin', 'admin_mobile')
        $configExcluded = $panthera -> config -> getKey('webroot.excluded', array(), 'array', 'webroot');

        $panthera -> logging -> startTimer();

        $roots = array (
            PANTHERA_DIR.'/templates/admin/webroot' => 'admin',
            SITE_DIR. '/content/templates/admin/webroot' => 'admin',
            PANTHERA_DIR.'/templates/admin_mobile/webroot' => 'admin_mobile',
            SITE_DIR. '/content/templates/admin_mobile/webroot' => 'admin_mobile',
            PANTHERA_DIR.'/templates/' .$mainTemplate. '/webroot' => $mainTemplate,
            SITE_DIR. '/content/templates/' .$mainTemplate. '/webroot' => $mainTemplate,
            PANTHERA_DIR. '/templates/_libs_webroot' => '_libs_webroot',
            SITE_DIR. '/templates/_libs_webroot' => '_libs_webroot'
        );

        // add templates from site configuration
        $customTemplates = array_merge($customTemplates, $configTemplates);

        if (!empty($customTemplates))
        {
            foreach ($customTemplates as $template => $mergeLib)
            {
                $roots[ PANTHERA_DIR. '/templates/' .$template. '/webroot' ] = $template;

                if ($mergeLib)
                    $roots[ SITE_DIR. '/content/templates/' .$template. '/webroot' ] = $template;
            }
        }

        $panthera->importModule('filesystem');

        // array with list of changes
        $changes = array();

        foreach ($roots as $dir => $templateName)
        {
            if (isset($configExcluded[$templateName]))
                continue;

            if (is_dir($dir))
            {
                $files = filesystem::scandirDeeply($dir, False);
                $panthera -> logging -> output('Found ' .count($files). ' files and/or directories in "' .$dir. '"', 'pantheraTemplate');

                // directories first need to be created
                foreach ($files as $file)
                {
                    if (is_dir($file))
                    {
                        // get directory address inside of root $dir
                        $chroot = str_replace($dir, '', $file);

                        if (!$chroot)
                            continue;

                        if (!is_dir(SITE_DIR. '/' .$chroot))
                        {
                            $panthera->logging->output('Creating a directory ' .SITE_DIR. '/' .$chroot, 'pantheraTemplate', True);
                            mkdir(SITE_DIR. '/' .$chroot);
                            $changes[] = array('status' => True, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'dir', 'chrootname' => $chroot, 'source' => $file);
                        } else
                            $changes[] = array('status' => False, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'dir', 'chrootname' => $chroot, 'source' => $file);
                    }
                }

                // now just simply copy files
                foreach ($files as $file)
                {
                    $chroot = '';

                    if (is_link($file))
                    {
                        $chroot = str_replace($dir, '', $file);
                        $file = readlink($file);

                        if (!is_file($file))
                        {
                            $file = SITE_DIR. '/' .str_replace(basename($chroot), '', $chroot).$file;
                        }
                    }

                    if(is_file($file))
                    {
                        // get file address inside of root $dir
                        if (!$chroot)
                            $chroot = str_replace($dir, '', $file);

                        // copy file if it does not exists
                        if (!is_file(SITE_DIR. '/' .$chroot))
                        {
                            $panthera->logging->output('Creating file ' .SITE_DIR. '/' .$chroot, 'pantheraTemplate', True);
                            copy($file, SITE_DIR. '/'.$chroot);
                            $changes[] = array('status' => True, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'file', 'chrootname' => $chroot, 'source' => $file);
                        } else {

                            // compare file dates
                            if (filemtime($file) > filemtime(SITE_DIR. '/' .$chroot))
                            {
                                $panthera->logging->output('Copying outdated file ' .SITE_DIR. '/' .$chroot, 'pantheraTemplate', True);
                                copy($file, SITE_DIR. '/'.$chroot);
                                $changes[] = array('status' => True, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'file', 'chrootname' => $chroot, 'source' => $file);
                            } else
                                $changes[] = array('status' => False, 'path' => SITE_DIR. '/' .$chroot, 'type' => 'file', 'chrootname' => $chroot, 'source' => $file);
                        }
                    }
                }
            } else {
                $panthera -> logging -> output('No such directory: "' .$dir. '"', 'pantheraTemplate');
            }
        }

        $panthera -> logging -> output ('WebrootMerge done', 'pantheraTemplate');

        return $changes;
    }

    /**
      * List all templates in /lib and /content
      *
      * @return array
      * @author Damian Kęska
      */

    public static function listTemplates($template=False)
    {
        $templates = array();

        if ($template == False)
        {
            $pantheraTemplates = @scandir(PANTHERA_DIR.'/templates');
            $contentTemplates = @scandir(SITE_DIR. '/content/templates');

            if ($pantheraTemplates)
            {
                foreach ($pantheraTemplates as $file)
                {
                    if ($file == '..' or $file == '.' or !is_dir(PANTHERA_DIR.'/templates/' .$file))
                        continue;

                    $templates[$file] = array('item' => PANTHERA_DIR.'/templates/' .$file, 'place' => 'lib');
                }
            }

            if ($contentTemplates)
            {
                foreach ($contentTemplates as $file)
                {
                    if ($file == '..' or $file == '.' or !is_dir(SITE_DIR.'/content/templates/' .$file))
                        continue;

                    $templates[$file] = array('item' => SITE_DIR.'/content/templates/' .$file, 'place' => 'content');
                }
            }

        } else {
            // list files of given template
            $pantheraFiles = @scandir(PANTHERA_DIR.'/templates/' .$template. '/templates');
            $contentFiles = @scandir(SITE_DIR.'/content/templates/' .$template. '/templates');

            if ($pantheraFiles)
            {
                foreach ($pantheraFiles as $file)
                {
                    if ($file == '..' or $file == '.' or !is_file(PANTHERA_DIR.'/templates/' .$template. '/templates/' .$file))
                        continue;

                    $templates[$file] = array('item' => PANTHERA_DIR.'/templates/' .$template. '/templates/' .$file, 'place' => 'lib');
                }
            }

            if ($contentFiles)
            {
                foreach ($contentFiles as $file)
                {
                    if ($file == '..' or $file == '.' or !is_file(SITE_DIR.'/content/templates/' .$template. '/templates/' .$file))
                        continue;

                    $templates[$file] = array('item' => SITE_DIR.'/content/templates/' .$template. '/templates/' .$file, 'place' => 'lib');
                }
            }

        }

        return $templates;
    }

    /*
     * Check if template exists
     *
     * @param string $baseTheme Directory name where template file is placed eg. admin
     * @param string $templateName Template file name eg. index.tpl
     * @return bool|null
     */

    public static function exists($baseTheme, $templateName)
    {
        if (is_file(getContentDir('templates/' .$baseTheme. '/templates/' .$templateName)))
        {
            return true;
        }
    }
}