<?php

/**
 * Comments management
 * Manager of comments for Panthera Admin Panel
 *
 * @package Panthera\ajaxpages\comments\admin
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license GNU LGPLv3, see license.txt
 */

class commentsAjaxControllerCore extends pageController
{
    protected $requirements = array(
        'admin/ui.pager',
    );

    protected $uiTitlebar = array(
        'Comments management'
    );

    protected $userPermissions = array();

    protected $actionPermissions = array(
        'deleteComment' => array('admin.can_delete_comment', 'admin.can_manage_comments'),
        'editComment' => array('admin.can_edit_comment', 'admin.can_manage_comments'),
        'holdComment' => array('admin.can_hold_comment', 'admin.can_manage_comments')
    );



    /**
     * Edit comment
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function editCommentAction()
    {
        $id = $_POST['edit_comment_id'];

        $comment = new userComment('id', $id);

        if (!$comment->exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Comment does not exist', 'comments')));

        if (strlen($_POST['edit_comment_content']) < 5)
            ajax_exit(array('status' => 'failed', 'message' => localize('Content is too short', 'comments')));

        if (strlen($_POST['edit_comment_group']) < 3)
            ajax_exit(array('status' => 'failed', 'message' => localize('Group is too short', 'comments')));

        if (strlen($_POST['edit_comment_objectid']) < 1)
            ajax_exit(array('status' => 'failed', 'message' => localize('Object ID is not set', 'comments')));

        $comment->content = filterInput($_POST['edit_comment_content'], "quotes,wysiwyg");
        $comment->group = $_POST['edit_comment_group'];
        $comment->object_id = $_POST['edit_comment_objectid'];
        $comment->allowed = (bool)$_POST['edit_comment_allowed'];
        $comment->modified = date("Y-m-d H:i:s");

        $comment -> save();

        ajax_exit(array('status' => 'success'));
    }



    /**
     * Delete comments, allowed massive management
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function deleteCommentAction()
    {
        $ids = $_POST['ids'];

        $id = explode(',', $ids);

        if (userComment::deleteComments($id))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed'));
    }



    /**
     * Hold comments, allowed massive management
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function holdCommentAction()
    {
        $ids = $_POST['ids'];

        $id = explode(',', $ids);

        if (userComment::holdComments($id))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed'));
    }



    /**
     * Get comment, return as array
     *
     * @author Mateusz Warzyński
     * @return array
     */

    public function getCommentAction()
    {
        $id = intval($_GET['id']);
        $comment = new userComment('id', $id);

        if (!$comment->exists())
            $this -> checkPermissions(false);

        $author = new pantheraUser('id', $comment->author_id);

        if ($author->exists())
            $login = $author->login;
        else
            $login = localize('Unknown', 'comments');


        ajax_exit(array(
            'status' => 'success',
            'content' => htmlspecialchars_decode($comment->content),
            'group' => $comment->group,
            'id' => $comment->id,
            'objectid' => $comment->object_id,
            'allowed' => $comment->allowed,
            'author_login' => $login,
            'posted' => $comment->posted
        ));
    }



    /**
     * Displays all comments, options to manage them
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        $this -> checkPermissions(array('admin.can_see_comments', 'admin.can_manage_comments'));

        $this -> dispatchAction();

        // Search bar
        $sBar = new uiSearchbar('uiTop');
        $sBar -> navigate(True);
        $sBar -> setQuery($_GET['query']);
        $sBar -> setAddress('?display=comments&cat=admin');
        $sBar -> addIcon($this -> panthera -> template -> getStockIcon('permissions'), '#', '?display=acl&cat=admin&popup=true&name=admin.can_see_comments,admin.can_manage_comments,admin.can_delete_comments,admin.can_hold_comments,admin.can_edit_comments', localize('Manage permissions'));

        $sBar -> addSetting('order', localize('Order by', 'comments'), 'select', array(
            'id' => array('title' => 'id', 'selected' => ($_GET['order'] == 'id')),
            'content' => array('title' => 'content', 'selected' => ($_GET['order'] == 'content')),
            'group' => array('title' => 'group', 'selected' => ($_GET['order'] == 'group')),
            'object_id' => array('title' => 'object_id', 'selected' => ($_GET['order'] == 'object_id')),
        ));

        $sBar -> addSetting('direction', localize('Direction', 'comments'), 'select', array(
            'ASC' => array('title' => localize('Ascending'), 'selected' => ($_GET['direction'] == 'ASC')),
            'DESC' => array('title' => localize('Descending'), 'selected' => ($_GET['direction'] == 'DESC'))
        ));


        // Get page from pager
        $page = intval($_GET['page']);
        $order = 'id'; $orderColumns = array('id', 'content', 'group', 'object_id');
        $direction = 'DESC';

        // order by
        if (in_array($_GET['order'], $orderColumns))
            $order = $_GET['order'];

        $w = new whereClause();

        // check if user used searchbar
        if ($_GET['query'])
        {
            $_GET['query'] = trim(strtolower($_GET['query'])); // strip unneeded spaces and make it lowercase

            if ($order != 'id')
                $w -> add( 'AND', $order, 'LIKE', '%' .$_GET['query']. '%');
            else
                $w -> add( 'AND', $order, '=', $_GET['query']);
        }

        // order by
        if (in_array($_GET['order'], $orderColumns))
            $order = $_GET['order'];

        if ($_GET['direction'] == 'DESC' or $_GET['direction'] == 'ASC')
            $direction = $_GET['direction'];

        $total = userComment::fetchComments($w, False, False, $order, $direction, True);

        // Pager stuff
        $uiPager = new uiPager('comments', $total, 'comments', 50);
        $uiPager -> setActive($page);
        $uiPager -> setLinkTemplatesFromConfig('messages.tpl');
        $limit = $uiPager -> getPageLimit();

        $comments = userComment::fetchComments($w, $limit[1], $limit[0], $order, $direction);

        $this -> panthera -> template -> push('commentsList', $comments);

        return $this -> panthera -> template -> compile('comments.tpl');
    }
}