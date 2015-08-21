<?php
/**
 * PF2 ORM tests
 *
 * @package Panthera\ORM\tests
 * @author Damian Kęska <damian.keska@fingo.pl>
 */

class ModelTest extends PantheraFrameworkTestCase
{
    public function testObjectFetchingById()
    {
        $this->setupDatabase();
        $this->app->database->query('INSERT INTO `users` (user_id, user_login, user_email) VALUES (1, \'phpunit\', \'phpunit@localhost\');', array());

        $user = new \Panthera\model\user(1);
        $this->assertEquals(true, ($user instanceof \Panthera\model\user));
    }
}