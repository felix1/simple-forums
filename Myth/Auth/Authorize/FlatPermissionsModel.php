<?php namespace Myth\Auth\Authorize;
/**
 * Sprint
 *
 * A set of power tools to enhance the CodeIgniter framework and provide consistent workflow.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Sprint
 * @author      Lonnie Ezell
 * @copyright   Copyright 2014-2015, New Myth Media, LLC (http://newmythmedia.com)
 * @license     http://opensource.org/licenses/MIT  (MIT)
 * @link        http://sprintphp.com
 * @since       Version 1.0
 */

use CodeIgniter\Model;

class FlatPermissionsModel extends Model {

	protected $table      = 'auth_permissions';
	protected $primaryKey = 'id';

	protected $returnType = 'array';

	protected $useSoftDeletes = false;
	protected $useTimestamps = false;

	protected $allowedFields = ['name', 'description'];

	protected $validation_rules = [
		'name'  => 'required|min_length[3]|max_length[255]|is_unique[auth_permissions.name]',
		'description' => 'max_length[255]'
	];

	/**
	 * Checks to see if a user, or one of their groups, has a specific
	 * permission.
	 *
	 * @param $user_id
	 * @param $permission_id
	 *
	 * @return bool
	 */
	public function doesUserGroupHavePermission(int $user_id, int $permission_id): bool
	{
		// Check the group
		$permissions = $this->builder()
			->join('auth_groups_permissions', 'auth_groups_permissions.permission_id = auth_permissions.id', 'inner')
			->join('auth_groups_users', 'auth_groups_users.group_id = auth_groups_permissions.group_id', 'inner')
			->where('auth_groups_users.user_id', (int)$user_id)
			->get()
			->getResultArray();

		if (! $permissions)
		{
			return false;
		}

		$permissions = (array)$permissions;

		$ids = array_column($permissions, 'permission_id');

		return in_array($permission_id, $ids);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether a user has a permission directly assigned to them.
	 *
	 * @param int $userID
	 * @param int $permissionID
	 *
	 * @return bool
	 */
	public function doesUserOwnPermission(int $userID, int $permissionID): bool
	{
		$permission = $this->db
			->table('auth_users_permissions')
			->where('user_id', $userID)
			->where('permission_id', $permissionID)
			->get();

		return ! empty($permission->getFirstRow());
	}

	/**
	 * Assigns a permission directly to a single user.
	 *
	 * @param int $permissionID
	 * @param int $userID
	 *
	 * @return bool
	 */
	public function assignToUser(int $permissionID, int $userID)
	{
		return $this->db
			->table('auth_users_permissions')
			->insert([
				'user_id' => $userID,
				'permission_id' => $permissionID
			]);
	}

	/**
	 * Removes a user-assigned permission from a user.
	 *
	 * @param int $permissionID
	 * @param int $userID
	 *
	 * @return mixed
	 */
	public function removeFromUser(int $permissionID, int $userID)
	{
		return $this->db
			->table('auth_users_permissions')
			->delete([
				'user_id' => $userID,
				'permission_id' => $permissionID
			]);
	}

}
