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
     * @author Damian Kęska <damian.keska@fingo.pl>
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('id', 'integer', [
            'length' => 9,
        ]);

        $table->addColumn('email', 'string', [
            'length' => 32,
        ]);
        $table->create();
    }
}
