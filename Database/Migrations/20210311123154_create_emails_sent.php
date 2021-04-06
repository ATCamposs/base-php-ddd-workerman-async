<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEmailsSent extends AbstractMigration
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
        $table = $this->table('emails_sent', ['id' => false]);
        $table->addColumn('email', 'string', ['limit' => 36, 'null' => false]);
        $table->addColumn('type', 'string', ['limit' => 36, 'null' => false]);
        $table->addColumn('created', 'datetime', ['null' => false]);
        $table->create();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('emails_sent');
        $table->drop();
        $table->save();
    }
}
