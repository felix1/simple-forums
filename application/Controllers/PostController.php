<?php namespace App\Controllers;

class PostController extends BaseController
{

	/**
	 * Show the new post form
	 *
	 * @param int $forumID
	 */
	public function newPost(int $forumID)
	{
		$this->render('posts/form');
	}

}
