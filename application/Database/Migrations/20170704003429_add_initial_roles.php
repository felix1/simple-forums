<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Services;
use Myth\Auth\Authorization\GroupModel;

class Migration_add_initial_roles extends Migration
{
	public function up()
	{
		$auth = new GroupModel();
		$auth->skipValidation(true);

		$auth->insert(['name' => 'admins', 'description' => 'Site Administrators']);
		$auth->insert(['name' => 'moderators', 'description' => 'Site moderators']);
		$auth->insert(['name' => 'members', 'description' => 'Registered users']);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->db->table('auth_groups')->truncate();
	}
}
