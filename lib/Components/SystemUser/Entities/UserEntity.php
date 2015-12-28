<?php
namespace Panthera\Components\SystemUser\Entities;

use Panthera\Components\Orm\ORMBaseFrameworkObject;

/**
 * User Entity class
 * Allows creating, deleting, updating objects that are stored in users table in database
 *
 * @orm
 * @package Panthera\Components\SystemUser\Entities
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class UserEntity extends ORMBaseFrameworkObject
{
    protected static $__ORM_Table = 'users';
    protected static $__ORM_IdColumn = 'user_id';

    /**
     * Here are put joined columns, rest of the mapping from main table is done automatic
     *
     * @var array $__ORM_MetaMapping
     */
    protected $__ORM_MetaMapping = [
        'group_name' => 'userPrimaryGroupName',
    ];

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
     * @orm virtualColumn
     * @column user_first_name
     * @var string
     */
    public $userFirstName   = null;

    /**
     * @orm virtualColumn
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
     * @orm leftOuterJoin
     * @join "groups.group_id" => "group_id as gid, group_name"
     *
     * @column user_primary_group
     * @var int
     */
    public $userPrimaryGroup;

    /**
     * @orm virtualColumn
     * @var string
     */
    protected $userPrimaryGroupName;

    /**
     * @return GroupEntity
     */
    public function getUserPrimaryGroup()
    {
        //return new GroupEntity($this->userPrimaryGroup);
    }

    /**
     * @return string
     */
    public function getUserPrimaryGroupName()
    {
        return $this->userPrimaryGroupName;
    }

    /**
     * Allows to change group name (change will be for all users)
     *
     * @param string $newName
     * @return $this
     */
    public function setPrimaryGroupName($newName)
    {
        $this->userPrimaryGroupName = $newName;
        return $this;
    }

    /**
     * Change user's password, encrypt using a strong algorithm
     *
     * @param string $password
     */
    public function changePassword($password)
    {
        $algorithm = $this->app->config->get('Passwords/algorithm', PASSWORD_BCRYPT);
        $cost = $this->app->config->get('Passwords/cost', 12);

        $this->userPassword = password_hash($password, $algorithm, [
            'cost' => $cost,
        ]);
    }

    /**
     * Validate entered password
     *
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return password_verify($password, $this->userPassword);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->userFirstName. ' ' .$this->userLastName;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->userLogin;
    }

    /**
     * @param string $login
     * @return $this
     */
    public function setLogin($login)
    {
        $this->userLogin = (string)$login;
        return $this;
    }

    /**
     * Controller's magic method that exposes external interface to public
     *
     * @Magic
     */
    public function __exposePublic()
    {
        return [
            'id'    => $this->userId,
            'name'  => $this->getName(),
            'login' => $this->userLogin,
        ];
    }
}