<?php namespace App\Controllers;

use Config\Forums;

class PostController extends BaseController
{

	/**
	 * Show the new post form
	 *
	 * @param int $forumID
	 */
	public function newPost(int $forumID)
	{
		$config = new Forums();
		$type = $this->request->getGetPost('type');

		$typeObject = ! empty($type)
			? $typeObject = $config->postFactory($type)
			: null;


		$this->render('posts/form', [
			'type' => $type,
			'types' => $config->postTypes,
			'typeObject' => $typeObject,
		]);
	}

}
