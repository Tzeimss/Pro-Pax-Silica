<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRolesEmployeesAndSchedules extends Migration
{
    public function up()
    {
        // Existing accounts remain customers by default. Employee and admin
        // privileges must be granted through trusted administrative tooling.
        $this->forge->addColumn('users', [
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 'customer',
                'after' => 'password_hash',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'role',
            ],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'department' => ['type' => 'VARCHAR', 'constraint' => 32],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('employees', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'assigned_employee_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'service_type' => ['type' => 'VARCHAR', 'constraint' => 32],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'requested'],
            'scheduled_start' => ['type' => 'DATETIME'],
            'scheduled_end' => ['type' => 'DATETIME', 'null' => true],
            'service_address' => ['type' => 'VARCHAR', 'constraint' => 255],
            'customer_notes' => ['type' => 'TEXT', 'null' => true],
            'check_in_at' => ['type' => 'DATETIME', 'null' => true],
            'check_in_latitude' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'check_in_longitude' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['customer_id', 'scheduled_start']);
        $this->forge->addKey(['assigned_employee_id', 'scheduled_start']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('customer_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_employee_id', 'employees', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('schedules', true);
    }

    public function down()
    {
        $this->forge->dropTable('schedules', true);
        $this->forge->dropTable('employees', true);
        $this->forge->dropColumn('users', ['role', 'is_active']);
    }
}
