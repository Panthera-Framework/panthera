<?php
namespace Panthera\model;

/**
 * User Entity class
 * Allows creating, deleting, updating objects that are stored in users table in database
 *
 * @orm
 * @package Panthera\modules\usersManagement
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class user extends \Panthera\database\ORMBaseFrameworkObject
{
    protected static $__orm_Table = 'users';
    protected static $__orm_IdColumn = 'user_id';

    /**
     * @orm
     * @column user_id
     * @var integer
     */
    public $userId          = null;

    /**
     * @orm
     * @required
     * @column user_login
     * @var string
     */
    public $userLogin       = null;

    /**
     * @orm
     * @column user_first_name
     * @var string
     */
    public $userFirstName   = null;

    /**
     * @orm
     * @column user_last_name
     * @var string
     */
    public $userLastName    = null;

    /**
     * @orm
     * @required
     * @column user_passwd
     * @var string
     */
    public $userPassword    = null;

    /**
     * @orm
     * @required
     * @column user_email
     * @var string
     */
    public $userEmail        = null;

    /**
     * @orm timestampOnCreate
     * @column user_created
     * @var string
     */
    public $userCreated      = null;

    /**
     * @orm timestampOnUpdate
     * @column user_updated
     * @var string
     */
    public $userUpdated      = null;

    /**
     * Controller's magic method that exposes external interface to public
     */
    public function __exposePublic()
    {
        return [
            'id'   => $this->userId,
            'name' => $this->userFirstName. ' ' .$this->userLastName,
        ];
    }
}