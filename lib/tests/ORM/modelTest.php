<?php
/**
 * PF2 ORM tests
 *
 * @package Panthera\ORM\tests
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class ModelTest extends PantheraFrameworkTestCase
{
    public function testObjectFetchingById()
    {
        $this->setupDatabase();

        try
        {
            $this->app->database->query('INSERT INTO `users` (user_id, user_login, user_email) VALUES (1, \'phpunit\', \'phpunit@localhost\');', array());
        }
        catch (\Exception $e)
        {
            // pass, as there is already this row in database
        }

        $user = new \Panthera\model\user(1);
        $this->assertEquals(true, ($user instanceof \Panthera\model\user));
        $this->assertEquals('phpunit', $user->userLogin);
        $this->assertEquals('phpunit@localhost', $user->userEmail);
    }
}