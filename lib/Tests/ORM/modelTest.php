<?php
use Panthera\Components\SystemUser\Entities\UserEntity;

/**
 * PF2 ORM tests
 *
 * @package Panthera\ORM\tests
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class ModelTest extends PantheraFrameworkTestCase
{
    /**
     * Test fetching records by id and representing as object
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function testObjectFetchingById()
    {
        $this->createTestUser();

        $user = new UserEntity(1);
        $this->assertEquals(true, ($user instanceof UserEntity));
        $this->assertEquals('phpunit', $user->userLogin);
        $this->assertEquals('phpunit@localhost', $user->userEmail);
        $this->assertEquals(1, $user->getId());
        $this->assertEquals(1, $user->__exposePublic()['id']);
        unset($user);
    }

    /**
     * Test creating database object with array
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testObjectCreatingWithArray()
    {
        $user = new UserEntity(array('user_id' => '2'));
        $this->assertEquals('2', $user->userId);
        unset($user);
    }

    /**
     * Test exception while invalid id has been provided
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testSelectObjectByInvalidId()
    {
        $this->setExpectedException('\Panthera\Classes\BaseExceptions\PantheraFrameworkException');
        $user = new UserEntity(2);
        unset($user);
    }

    /**
     * Test fetching user object by id
     *
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function testFetchUserObject()
    {
        $this->createTestUser();

        $result = UserEntity::fetch(array('|=|user_id' => 1));
        $this->assertSame('phpunit@localhost', $result[0]->userEmail);
        unset($result);
    }

    /**
     * Our "dataProvider"
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function createTestUser()
    {
        try
        {
            $this->app->database->query('INSERT INTO `users` (user_id, user_login, user_email) VALUES (1, \'phpunit\', \'phpunit@localhost\');', array());
        }
        catch (\Exception $e)
        {
            // pass, as there is already this row in database
        }
    }
}