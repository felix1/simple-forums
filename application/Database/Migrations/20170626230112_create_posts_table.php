<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class Migration_create_posts_table
 *
 * The POST contains the actual messages.
 *
 * @package App\Database\Migrations
 */
class Migration_create_posts_table extends Migration
{
	public function up()
	{
		$this->forge->addField([
		    'id'            => ['type' => 'int', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'forum_id'      => ['type' => 'smallint', 'constraint' => 5, 'default' => 0],
            'thread_id'     => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'user_id'       => ['type' => 'int', 'constraint' => 11],
            'type'          => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
		    'markup'        => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
		    'title'         => ['type' => 'varchar', 'constraint' => 255],
		    'body'          => ['type' => 'text'],
		    'html'          => ['type' => 'text'],
            'created_at'    => ['type' => 'datetime'],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);

		$this->forge->addKey('id', true);
		$this->forge->addKey('thread_id');
		$this->forge->addKey('user_id');

		$this->forge->createTable('posts', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('posts');
	}
}
