<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePortalFeatures extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'body' => ['type' => 'TEXT'],
            'category' => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'notice'],
            'read_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notifications', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 160],
            'message' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('support_tickets', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'target_role' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'body' => ['type' => 'TEXT'],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('announcements', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'schedule_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'employee_id' => ['type' => 'INT', 'unsigned' => true],
            'severity' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'normal'],
            'description' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'open'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('schedule_id', 'schedules', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('incidents', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'schedule_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'currency' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'PHP'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'paid'],
            'reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'paid_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'paid_at']);
        $this->forge->addForeignKey('schedule_id', 'schedules', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payments', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kind' => ['type' => 'VARCHAR', 'constraint' => 16],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'TEXT', 'null' => true],
            'price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['kind', 'is_active']);
        $this->forge->createTable('catalog_items', true);

        $this->db->table('catalog_items')->insertBatch([
            ['kind' => 'service', 'name' => 'Consultation', 'price' => 0, 'is_active' => 1],
            ['kind' => 'service', 'name' => 'Designing', 'price' => 0, 'is_active' => 1],
            ['kind' => 'service', 'name' => 'Permitting', 'price' => 0, 'is_active' => 1],
            ['kind' => 'service', 'name' => 'Installation', 'price' => 0, 'is_active' => 1],
            ['kind' => 'service', 'name' => 'Maintenance', 'price' => 0, 'is_active' => 1],
            ['kind' => 'service', 'name' => 'Repair', 'price' => 0, 'is_active' => 1],
            ['kind' => 'service', 'name' => 'Monitoring', 'price' => 0, 'is_active' => 1],
            ['kind' => 'product', 'name' => 'Solar panels', 'price' => 0, 'is_active' => 1],
            ['kind' => 'product', 'name' => 'Inverters', 'price' => 0, 'is_active' => 1],
            ['kind' => 'product', 'name' => 'Batteries', 'price' => 0, 'is_active' => 1],
            ['kind' => 'product', 'name' => 'Racking and mounting', 'price' => 0, 'is_active' => 1],
            ['kind' => 'product', 'name' => 'Wires', 'price' => 0, 'is_active' => 1],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('catalog_items', true);
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('incidents', true);
        $this->forge->dropTable('announcements', true);
        $this->forge->dropTable('support_tickets', true);
        $this->forge->dropTable('notifications', true);
    }
}
