<?php namespace App\Database\Migrations;

use Config\Services;
use CodeIgniter\Database\Migration;

class Migration_create_initial_roles_permissions extends Migration
{
	public function up()
	{
		/**
		 * @var \Myth\Auth\Authorization\FlatAuthorization
		 */
		$auth = Services::authorization();

		$auth->createPermission('access-admin', 'Allows access to the admin area.');
		$auth->createPermission('manage-users', 'Create and edit users.');
		$auth->createPermission('delete-users', 'Delete users.');
		$auth->createPermission('manage-roles', 'Manage Roles and Permissions.');
		$auth->createPermission('manage-settings', 'Manage general site settings.');
		$auth->createPermission('post', 'Can post in forums.');
		$auth->createPermission('manage-forums', 'Can manage forums and their details.');
		$auth->createPermission('manage-posts', 'Can manage posts by others.');

		$auth->createGroup('superadmins');
		$auth->createGroup('admins');
		$auth->createGroup('moderators');
		$auth->createGroup('users');

		$auth->addPermissiontoGroup('access-admin', 'superadmins');
		$auth->addPermissiontoGroup('manage-users', 'superadmins');
		$auth->addPermissiontoGroup('delete-users', 'superadmins');
		$auth->addPermissiontoGroup('manage-roles', 'superadmins');
		$auth->addPermissiontoGroup('manage-settings', 'superadmins');
		$auth->addPermissiontoGroup('post', 'superadmins');
		$auth->addPermissiontoGroup('manage-forums', 'superadmins');
		$auth->addPermissiontoGroup('manage-posts', 'superadmins');

		$auth->addPermissiontoGroup('access-admin', 'admins');
		$auth->addPermissiontoGroup('manage-users', 'admins');
		$auth->addPermissiontoGroup('manage-roles', 'admins');
		$auth->addPermissiontoGroup('manage-settings', 'admins');
		$auth->addPermissiontoGroup('post', 'admins');
		$auth->addPermissiontoGroup('manage-posts', 'admins');

		$auth->addPermissiontoGroup('access-admin', 'moderators');
		$auth->addPermissiontoGroup('post', 'moderators');
		$auth->addPermissiontoGroup('manage-posts', 'moderators');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$auth = Services::authorization();

		$auth->deletePermission($auth->getPermissionID('access-admin'));
		$auth->deletePermission($auth->getPermissionID('manage-users'));
		$auth->deletePermission($auth->getPermissionID('delete-users'));
		$auth->deletePermission($auth->getPermissionID('manage-roles'));

		$auth->deleteGroup($auth->getGroupID('superadmins'));
		$auth->deleteGroup($auth->getGroupID('admins'));
		$auth->deleteGroup($auth->getGroupID('moderators'));
		$auth->deleteGroup($auth->getGroupID('users'));
	}
}
