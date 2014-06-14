<?php
/**
 * Merge serialized arrays and json files
 *
 * @package Panthera\core\adminUI\debug\mergephps
 * @author Damian Kęska
 * @license GNU LGPLv3
 */

/**
 * Merge serialized arrays and json files
 *
 * @package Panthera\core\adminUI\debug\mergephps
 * @author Damian Kęska
 */

class mergephpsAjaxControllerSystem extends pageController
{
    protected $uiTitlebar = array(
        'Merge serialized arrays and json files', 'debug'
    );

    protected $permissions = array(
        'admin.mergephps' => array('Merge serialized arrays and json files', 'debug'),
    );

    /**
     * Main function
     *
     * @return string
     */

    public function display()
    {
        $a = $b = array();

        if (isset($_POST['a']) and $_POST['b'])
        {
            $a = $this -> getArray($_POST['a']);
            $b = $this -> getArray($_POST['b']);

            ajax_exit(array(
                'status' => 'success',
                'a' => json_encode($a, JSON_PRETTY_PRINT),
                'b' => json_encode(array_merge($a, $b), JSON_PRETTY_PRINT),
            ));
        }

        $this -> panthera -> template -> push(array(
            'a' => json_encode($a, JSON_PRETTY_PRINT),
            'b' => json_encode(array_merge($a, $b), JSON_PRETTY_PRINT),
        ));

        return $this -> panthera -> template -> compile('mergephps.tpl');
    }

    /**
     * Unserialize/decode array
     *
     * @param string $input Input array in any serialized format
     * @return array
     */

    public function getArray($input)
    {
        if (@json_decode($input))
            return json_decode($input, true);

        if (@unserialize($input))
            return unserialize($input);

        return array();
    }
}