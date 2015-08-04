<?php
require_once PANTHERA_FRAMEWORK_PATH. '/vendor/autoload.php';

use \Phinx\Migration\AbstractMigration;

class AutoCreateUserTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('users');
        $table->addColumn('name', 'string', ['length' => 100])
            ->addColumn('email', 'string')
            ->create();
    }
}