<?php
/**
 * Simple JSON array editor
 *
 * @package Panthera\core\adminUI\jsonedit
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Simple JSON array editor
 *
 * @package Panthera\core\adminUI\jsonedit
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class _popup_jsoneditAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Array editor', 'debug'
    );

    protected $requirements = array(
        'admin/ui.pager',
    );

    protected $permissions = array(
        'admin.jsonedit' => array('Array editor', 'debug'),
    );

    /**
     * Main function
     *
     * @return string
     */

    public function display()
    {
        if (isset($_POST['jsonedit_content']))
        {
            $this -> panthera -> session -> set('jsonedit_content', $_POST['jsonedit_content']);

            // allow serialized arrays as input
            if (@unserialize($_POST['jsonedit_content']))
                $_POST['jsonedit_content'] = json_encode(unserialize($_POST['jsonedit_content']));

            $response = serialize(json_decode($_POST['jsonedit_content'], true));
            $response = $this -> parseResponse($response, $_POST['responseType']);

            // accept CSV
            if (!$response or in_array($response, array('null', 'NULL', "null\n", 'N;')))
            {
                $t = explode(',', str_replace(', ', ',', $_POST['jsonedit_content']));

                if ($t)
                    $response = $this -> parseResponse($t, $_POST['responseType']);
            }

            ajax_exit(array(
                'status' => 'success',
                'result' => stripslashes($response),
            ));
        }

        $array = unserialize(stripslashes(base64_decode($_GET['input'])));

        if (version_compare(phpversion(), '5.4.0', '>'))
            $code = json_encode($array, JSON_PRETTY_PRINT);
        else
            $code = json_encode($array);
        // remember last code after page refresh
        if ($array == False and !isset($_GET['popup']))
        {
            $code = '';

            if ($this -> panthera -> session -> exists('jsonedit_content'))
                $code = $this -> panthera -> session -> get('jsonedit_content');
        }

        $this -> panthera -> template -> push (array(
            'popup' => $_GET['popup'],
            'code' => $code,
            'callback' => $_GET['callback'],
            'callback_arg' => $_GET['callback_arg'],
        ));

        return $this -> panthera -> template -> compile('_popup_jsonedit.tpl');
    }

    /**
     * Parse response
     *
     * @param string|array $response Serialized array or just array
     * @param string $type Output type eg. json, var_dump, print_r, var_export
     * @return string|bool
     */

    public function parseResponse($response, $type)
    {
        if (!is_array($response))
            $response = unserialize($response);

        if (!$response)
            return false;

        switch ($type)
        {
            case 'print_r':
                $response = print_r($response, True);
            break;

            case 'var_dump':
                ob_start();
                var_dump($response);
                $response = str_replace('=&gt; ', '=> ', strip_tags(ob_get_clean()));
            break;

            case 'json':
                // and json pretty printed
                if (version_compare(phpversion(), '5.4.0', '>'))
                    $response = json_encode($response, JSON_PRETTY_PRINT);
                else
                    $response = json_encode($response);
            break;

            case 'var_export':
                $response = var_export($response, True);
            break;

            case 'csv':
                $response = implode(', ', $response);
            break;

            default:
                $response = serialize($response);
            break;
        }

        return $response;
    }
}