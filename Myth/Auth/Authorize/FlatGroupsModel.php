<?php namespace Myth\Auth\Authorize;

use CodeIgniter\Model;

class FlatGroupsModel extends Model {

	protected $table      = 'auth_groups';
	protected $primaryKey = 'id';

	protected $returnType = 'array';

	protected $useSoftDeletes = false;
	protected $useTimestamps = false;

	protected $allowedFields = ['name', 'description'];

	protected $validationRules = [
		'name'  => 'required|min_length[3]|max_length[255]|is_unique[auth_groups.name]',
		'description' => 'max_length[255]'
	];

	//--------------------------------------------------------------------
	// Users
	//--------------------------------------------------------------------

	/**
	 * Adds a single user to a single group.
	 *
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return object
	 */
	public function addUserToGroup($user_id, $group_id)
	{
	    $data = [
		    'user_id'   => (int)$user_id,
		    'group_id'  => (int)$group_id
	    ];

		return $this->db->table('auth_groups_users')->insert($data);
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single user from a single group.
	 *
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return bool
	 */
	public function removeUserFromGroup($user_id, $group_id)
	{
	    return $this->db->table('auth_groups_users')
			->where([
		    'user_id' => (int)$user_id,
		    'group_id' => (int)$group_id
	    ])->delete();
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single user from all groups.
	 *
	 * @param $user_id
	 *
	 * @return mixed
	 */
	public function removeUserFromAllGroups($user_id)
	{
	    return $this->db->where('user_id', (int)$user_id)
		                ->delete('auth_groups_users');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all groups that a user is a member of.
	 *
	 * @param $user_id
	 *
	 * @return object
	 */
	public function getGroupsForUser($user_id)
	{
	    return $this->select('auth_groups_users.*, auth_groups.name, auth_groups.description')
		            ->join('auth_groups_users', 'auth_groups_users.group_id = auth_groups.id', 'left')
		            ->where('user_id', $user_id)
		            ->asArray()
		            ->findAll();
	}

	//--------------------------------------------------------------------



	//--------------------------------------------------------------------
	// Permissions
	//--------------------------------------------------------------------

	public function addPermissionToGroup($permission_id, $group_id)
	{
		$data = [
			'permission_id' => (int)$permission_id,
			'group_id'      => (int)$group_id
		];

	    return $this->db->insert('auth_groups_permissions', $data);
	}

	//--------------------------------------------------------------------


	/**
	 * Removes a single permission from a single group.
	 *
	 * @param $permission_id
	 * @param $group_id
	 *
	 * @return mixed
	 */
	public function removePermissionFromGroup($permission_id, $group_id)
	{
	    return $this->db->where([
		    'permission_id' => $permission_id,
		    'group_id'      => $group_id
	    ])->delete('auth_groups_permissions');
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single permission from all groups.
	 *
	 * @param $permission_id
	 *
	 * @return mixed
	 */
	public function removePermissionFromAllGroups($permission_id)
	{
	    return $this->db->where('permission_id', $permission_id)
		                ->delete('auth_groups_permissions');
	}

	//--------------------------------------------------------------------

}
