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
			'avatar'           => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'password_hash'    => ['type' => 'varchar', 'constraint' => 255],
			'reset_hash'       => ['type' => 'varchar', 'constraint' => 40, 'null' => true],
			'activate_hash'    => ['type' => 'varchar', 'constraint' => 40, 'null' => true],
			'status'           => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'status_message'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'active'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
			'force_pass_reset' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
			'deleted'          => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
			'created_at'       => ['type' => 'datetime', 'null' => true],
			'updated_at'       => ['type' => 'datetime', 'null' => true],
		]);

		$this->forge->addKey('id', true);
		$this->forge->addKey('email');

		$this->forge->createTable('users', true);

		// User Profile
		$this->forge->addField([
			'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
			'phone'         => ['type' => 'varchar', 'constraint' => 20, 'null' => true],
			'personal_url'  => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'business_url'  => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'facebook_url'  => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'twitter_url'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'gplus_url'     => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'about'         => ['type' => 'text', 'null' => true],
			'social_public' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
			'show_email'    => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
			'show_phone'    => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
			'views'         => ['type' => 'integer', 'constraint' => 11, 'default' => 0],
			'created_at'       => ['type' => 'datetime', 'null' => true],
			'updated_at'       => ['type' => 'datetime', 'null' => true],
		]);

		$this->forge->addKey('user_id', true);

		$this->forge->createTable('user_profiles', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('users');
		$this->forge->dropTable('user_profiles');
	}
}
