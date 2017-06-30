<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_users_table extends Migration
{
	public function up()
	{
		// Users
		$this->forge->addField([
			'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
			'email'            => ['type' => 'varchar', 'constraint' => 255],
			'username'         => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
			'password_hash'    => ['type' => 'varchar', 'constraint' => 255],
			'reset_hash'       => ['type' => 'varchar', 'constraint' => 40, 'null' => true],
			'activate_hash'    => ['type' => 'varchar', 'constraint' => 40, 'null' => true],
			'created_on'       => ['type' => 'datetime', 'default' => '0000-00-00 00:00:00'],
			'status'           => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'status_message'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'active'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
			'deleted'          => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
			'force_pass_reset' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
		]);

		$this->forge->addKey('id', true);
		$this->forge->addKey('email');

		$this->forge->createTable('users', true);

		// User Meta
		$this->forge->addField([
			'user_id'    => ['type'       => 'int', 'constraint' => 11, 'unsigned'   => true,],
			'meta_key'   => ['type'       => 'varchar', 'constraint' => 255,],
			'meta_value' => ['type' => 'text', 'null' => true,],
		]);

		$this->forge->addKey(['user_id', 'meta_key'], true);

		$this->forge->createTable('user_meta', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('users');
		$this->forge->dropTable('user_meta');
	}
}
