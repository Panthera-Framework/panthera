<?php
/**
 * Access Control Lists debugging
 *
 * @package Panthera\core\adminUI\debug\debug.acl
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Access Control Lists debugging
 *
 * @package Panthera\core\adminUI\debug\debug.acl
 * @author Damian Kęska
 */

class debug_aclAjaxControllerSystem extends pageController
{
    protected $uiTitlebar = array(
        'Access Control Lists debugging', 'debug',
    );

    protected $permissions = array('admin.debug.acl' => array('Access Control Lists debugging', 'debug'));

    /**
     * Main function
     *
     * @return string
     */

    public function display()
    {
        $tag = $type = $value = $user = False;

        if (isset($_GET['tag']) and $_GET['tag'])
            $tag = $_GET['tag'];

        if (isset($_GET['type']) and $_GET['type'])
            $type = $_GET['type'];

        if (isset($_GET['value']) and $_GET['value'])
            $value = $_GET['value'];

        if (isset($_GET['user']) and $_GET['user'])
            $user = $_GET['user'];

        $search = meta::getTags($tag, $type, $value, $user, true);

        $sBar = new uiSearchbar('uiTop');
        $sBar -> navigate(True);

        if ($sBar -> getQuery())
            $search = $sBar -> filterData($search, $sBar -> getQuery());

        $this -> panthera -> template -> push(array(
            'tags' => $search,
            'filterTag' => $tag,
            'filterType' => $type,
            'filterValue' => $value,
            'filterUser' => $user,
        ));

        return $this -> panthera -> template -> compile('debug.acl.tpl');
    }
}