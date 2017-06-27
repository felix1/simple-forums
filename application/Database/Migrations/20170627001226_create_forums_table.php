<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_forums_table extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'            => ['type' => 'smallint', 'constraint' => 5, 'auto_increment' => true, 'unsigned' => true],
			'name'          => ['type' => 'varchar', 'constraint' => 255],
			'description'   => ['type' => 'text'],
			'is_category'   => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
			'threads'       => ['type' => 'int', 'constraint' => 11, 'default' => 0],
			'posts'         => ['type' => 'int', 'constraint' => 11, 'default' => 0],
			'last_post'     => ['type' => 'int', 'constraint' => 11, 'default' => 0],
			'created_at'    => ['type' => 'datetime'],
			'updated_at'    => ['type' => 'datetime', 'null' => true],
			'deleted_at'    => ['type' => 'datetime', 'null' => true],
		]);

		$this->forge->addKey('id', true);
		$this->forge->addKey('last_post');

		$this->forge->createTable('forums');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('forums');
	}
}
