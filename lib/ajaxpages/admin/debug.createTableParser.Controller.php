<?php
/**
 * Parse create table statement and output in json/serialized format
 *
 * @package Panthera\core\system\db
 * @author Damian Kęska
 * @license GNU LGPLv3
 */

/**
 * Parse create table statement and output in json/serialized format
 *
 * package Panthera\core\system\db
 * @author Damian Kęska
 */

class debug_createTableParserAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Database CREATE TABLE statement parser', 'debug'
    );

    protected $permissions = array(
        'admin.createTableParser' => array('Database CREATE TABLE statement parser', 'debug'),
    );

    /**
     * Main function, displays page
     *
     * @return string
     */

    public function display()
    {
        $this -> panthera -> template -> push('code', $_POST['input']);

        if (isset($_POST['input']) or isset($_REQUEST['template']))
        {
            // take database template as input
            if (isset($_REQUEST['template']) and $_REQUEST['template'])
            {
                $dir = getContentDir('database/templates/' .addslashes($_REQUEST['template']). '.sql');

                if ($dir)
                    $_POST['input'] = file_get_contents($dir);
            }

            $structure = new SQLStructure($_POST['input']);
            $result = $structure -> getParsedArray();

            if ($result)
            {
                switch ($_REQUEST['responseType'])
                {
                    case 'json':
                        $result = json_encode($result);
                    break;

                    case 'var_dump':
                        $result = r_dump($result);
                    break;

                    case 'print_r':
                        $result = print_r($result, True);
                    break;

                    case 'var_export':
                        $result = var_export($result, true). ';';
                    break;

                    default:
                        $result = serialize($result);
                    break;
                }
            }


            if (isset($_GET['ajax']) and $_GET['ajax'] != '0')
            {
                if ($result)
                {
                    ajax_exit(array(
                        'status' => 'success',
                        'resultText' => $result,
                    ));
                }

                ajax_exit(array(
                    'status' => 'failed',
                ));
            }

            $this -> panthera -> template -> push(array(
                'code' => $result,
                'responseType' => strip_tags($_REQUEST['responseType']),
                'content' => strip_tags($_POST['input']),
            ));
        }

        return $this -> panthera -> template -> compile('debug.createTableParser.tpl');
    }
}