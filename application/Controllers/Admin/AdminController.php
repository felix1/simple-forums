<?php namespace App\Controllers\Admin;

use App\Controllers\AuthController;

class AdminController extends AuthController
{
	protected $theme = 'admin';

	public function __construct()
	{
		parent::__construct();

		$this->restrictWithPermissions('access-admin');
	}
}
