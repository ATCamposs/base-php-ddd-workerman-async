<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        $table = $this->table('users', ['id' => false, 'primary_key' => 'uuid']);
        $table->addColumn('uuid', 'string', ['limit' => 36, 'null' => false]);
        $table->addColumn('user_name', 'string', ['limit' => 32, 'null' => false]);
        $table->addColumn('email', 'string', ['limit' => 36, 'null' => false]);
        $table->addColumn('password', 'string', ['limit' => 128, 'null' => false]);
        $table->addColumn('active', 'boolean', ['default' => false, 'null' => false]);
        $table->addColumn('access_level', 'integer', ['default' => 0, 'limit' => 1, 'null' => false]);
        $table->addColumn('activation_hash', 'string', ['limit' => 64, 'null' => true]);
        $table->addColumn('created', 'datetime', ['null' => false]);
        $table->addColumn('modified', 'datetime', ['default' => null, 'null' => true]);
        $table->addIndex(['uuid', 'email'], ['unique' => true]);
        $table->create();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('users');
        $table->drop();
        $table->save();
    }
}
