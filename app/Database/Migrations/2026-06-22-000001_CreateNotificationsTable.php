<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('notifications')) {
            return;
        }

        $this->forge->addField([
            'id_notif' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'id_order' => [
                'type' => 'VARCHAR',
                'constraint' => '11',
                'null' => false,
            ],
            'id_meja' => [
                'type' => 'VARCHAR',
                'constraint' => '11',
                'null' => false,
            ],
            'nama_pelanggan' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['unread', 'read'],
                'default' => 'unread',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id_notif', true);
        $this->forge->addForeignKey('id_order', 'orders', 'id_order', '', 'CASCADE');
        $this->forge->addForeignKey('id_meja', 'tables_meja', 'id_meja', '', 'CASCADE');
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications', true);
    }
}
