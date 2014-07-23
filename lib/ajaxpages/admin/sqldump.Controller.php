<?php
/**
 * Make backup of database
 *
 * @package Panthera\core\system\db
 * @author Damian Kęska
 * @license LGPLv3
 */

ini_set('memory_limit', '256M');

/**
 * Make backup of database page controller
 *
 * @package Panthera\core\system\db
 * @author Damian Kęska
 */

class sqldumpAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.database.backups' => array(
            'Backup your database to prevent data loss', 'database',
        ),
    );
    
    protected $uiTitlebar = array('Backup your database to prevent data loss', 'database');


    /**
     * Manage sql dump
     *
     * @author Damian Kęska
     * @return null
     */

    public function createAction()
    {
        if (!isset($_POST['action']) or $_POST['action'] != 'create')
            ajax_exit(array(
                'status' => 'failed',
            ));
            
        if ($this->panthera->db->getSocketType() == 'mysql')
            $name = $this->panthera->config->getKey('db_name'). '-' .date('Y.m.d_H:i:s'). '.sql';
        else
            $name = date('Y.m.d_H:i:s'). '-' .$panthera->config->getKey('db_file'). '.sql';

        $dump = SQLDump::make();

        if ($dump)
        {
            rename($dump, SITE_DIR. '/content/backups/db/' .$name);
            
            ajax_exit(array(
                'status' => 'success',
            ));
        }

        ajax_exit(array(
            'status' => 'failed',
        ));
    }



    /**
     * Manage "sqldump" cronjob
     *
     * @author Damian Kęska
     * @return null
     */

    public function manageCronjobAction()
    {
        $job = new crontab('jobname', 'sqldump');

        if (!$job -> exists() and $_POST['management'] == 'createJob') {
            crontab::createJob('sqldump', array('SQLDump', 'cronjob'), '', '*', '*', '*/7'); // 7 days interval by default
            $job = new crontab('jobname', 'sqldump');
        }

        if (!$job -> exists() and $_POST['management'] == 'createJob')
            ajax_exit(array(
                'status' => 'failed',
                'message' => slocalize('Cannot create a cronjob, database error', 'crontab'),
            ));

        if ($_POST['management'] == 'removeJob')
            crontab::removeJob($job -> jobid);

        ajax_exit(array(
            'status' => 'success',
        ));
    }



    /**
      * Display page template
      *
      * @author Damian Kęska
      * @return string
      */

    public function display()
    {
        if (isset($_GET['get']))
        {
            $file = addslashes(str_replace('../', '', $_GET['get']));

            if (is_file(SITE_DIR. '/content/backups/db/' .$file))
            {
                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' .$file. '"');
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Sat, 26 Jul ".(intval(date('Y'))-5)." 05:00:00 GMT");
                print(file_get_contents(SITE_DIR. '/content/backups/db/' .$file));
                pa_exit();
            }
        }

        $this -> dispatchAction();

        $uiPager = new uiPager('adminSQLDumps', SQLDump::getSQLDumps(False));
        $uiPager -> setActive(intval($_GET['page']));
        $uiPager -> setLinkTemplatesFromConfig('sqldump.tpl');
        $limit = $uiPager -> getPageLimit();
        $dumps = SQLDump::getSQLDumps($limit[0], $limit[1]);

        $dumpsTpl = array();

        foreach ($dumps as $dump)
        {
            $dumpsTpl[] = array(
                'name' => basename($dump),
                'size' => filesystem::bytesToSize(filesize($dump)),
                'date' => date($this -> panthera -> dateFormat, filemtime($dump))
            );
        }

        $job = new crontab('jobname', 'sqldump');

        $this -> panthera -> template -> push('serviceAvaliable', $job -> exists());
        $this -> panthera -> template -> push('dumps', $dumpsTpl);

        return $this -> panthera -> template -> compile('sqldump.tpl');
    }
}