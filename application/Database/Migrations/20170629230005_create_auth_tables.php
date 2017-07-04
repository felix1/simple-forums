<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_auth_tables extends Migration
{
	public function up()
	{
		// Auth Login Attempts
		$this->forge->addField([
			'id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
			'type' => ['type' => 'varchar', 'constraint' => 64, 'null' => false, 'default' => 'app', 'after' => 'id'],
			'ip_address' => ['type' => 'varchar', 'constraint' => 255, 'null' => true, 'after' => 'type'],
			'user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'ip_address'],
			'datetime' => ['type' => 'datetime'],
		]);
		$this->forge->addKey('id', true);
		$this->forge->addKey('email');
		$this->forge->addKey('user_id');

		$this->forge->createTable('auth_login_attempts', true);

		// Auth Logins
		$this->forge->addField([
			'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
			'user_id'    => ['type' => 'int', 'constraint' => 11,],
			'ip_address' => ['type' => 'varchar', 'constraint' => 40, 'null' => true],
			'datetime'   => ['type' => 'datetime',],
		]);
		$this->forge->addKey('id', true);
		$this->forge->addKey('email');

		$this->forge->createTable('auth_logins', true);

		// Auth Tokens
		$this->forge->addField([
			'email'   => ['type'       => 'varchar', 'constraint' => 255,],
			'hash'    => ['type'       => 'char', 'constraint' => 40,],
			'created' => ['type' => 'datetime',],
		]);
		$this->forge->addKey(['email', 'hash']);

		$this->forge->createTable('auth_tokens', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('auth_login_attempts', true);
		$this->forge->dropTable('auth_logins', true);
		$this->forge->dropTable('auth_tokens', true);
	}
}
