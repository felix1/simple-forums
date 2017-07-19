<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_threads_table extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'            => ['type' => 'int', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
			'user_id'       => ['type' => 'int', 'constraint' => 11, 'default' => 0],
			'forum_id'      => ['type' => 'smallint', 'constraint' => 5, 'default' => 0],
			'title'         => ['type' => 'varchar', 'constraint' => 255],
			'first_post'    => ['type' => 'int', 'constraint' => 11, 'default' => 0, 'null' => true],
			'last_post'     => ['type' => 'int', 'constraint' => 11, 'default' => 0, 'null' => true],
			'view_count'    => ['type' => 'int', 'constraint' => 20, 'default' => 0, 'null' => true],
			'post_count'    => ['type' => 'int', 'constaint' => 20, 'default' => 0, 'null' => true],
			'created_at'    => ['type' => 'datetime'],
			'updated_at'    => ['type' => 'datetime', 'null' => true],
			'deleted_at'    => ['type' => 'datetime', 'null' => true],
		]);

		$this->forge->addKey('id', true);
		$this->forge->addKey('user_id');
		$this->forge->addKey('forum_id');

		$this->forge->createTable('threads');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('threads');
	}
}
