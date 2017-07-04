<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_authorization_tables extends Migration
{
	public function up()
	{
		/**
		 * Groups Table
		 */
		$fields = [
			'id'    => ['type'  => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
			'name'  => ['type' => 'varchar', 'constraint' => 255],
			'description' => ['type' => 'varchar', 'constraint' => 255]
		];

		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('auth_groups', true);
		/**
		 * Permissions Table
		 */
		$fields = [
			'id'    => ['type'  => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
			'name'  => ['type' => 'varchar', 'constraint' => 255],
			'description' => ['type' => 'varchar', 'constraint' => 255]
		];

		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('auth_permissions', true);

		/**
		 * Groups/Permissions Table
		 */
		$fields = [
			'group_id'    => ['type'  => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
			'permission_id'    => ['type'  => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
		];

		$this->forge->addField($fields);
		$this->forge->addKey(['group_id', 'permission_id']);
		$this->forge->createTable('auth_groups_permissions', true);

		/**
		 * Users/Groups Table
		 */
		$fields = [
			'group_id'    => ['type'  => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
			'user_id'    => ['type'  => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0]
		];

		$this->forge->addField($fields);
		$this->forge->addKey(['group_id', 'user_id']);
		$this->forge->createTable('auth_groups_users', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('auth_groups');
		$this->forge->dropTable('auth_permissions');
		$this->forge->dropTable('auth_groups_permissions');
		$this->forge->dropTable('auth_groups_users');
	}
}
