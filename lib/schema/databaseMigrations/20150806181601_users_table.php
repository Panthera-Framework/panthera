<?php
use Phinx\Migration\AbstractMigration;

/**
 * Class UsersTable
 *
 * @author Damian Kęska <damian.keska@fingo.pl>
 * @package Panthera\modules\usersManagement\migrations
 */
class UsersTable extends AbstractMigration
{
    /**
     * Initialy create a users table
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function change()
    {
        $table = $this->table('users', array('id' => 'user_id'));
        $table->addColumn('user_login',   'string',   array('limit' => 32))
              ->addColumn('user_passwd',  'string',   array('limit' => 64))
              ->addColumn('user_email',   'string',   array('limit' => 32))
              ->addColumn('user_created', 'datetime', array('default' => 'CURRENT_TIMESTAMP'))
              ->addColumn('user_updated', 'datetime', array('null' => true))
              ->create();
    }
}
