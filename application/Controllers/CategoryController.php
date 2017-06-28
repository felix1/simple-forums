<?php namespace App\Controllers;

use App\Domains\Forums\ForumModel;

class CategoryController extends BaseController
{
	protected $forums;

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->forums = new ForumModel();
	}

	public function index()
	{
		$categories = $this->forums->findCategories();
		$categories = $this->forums->fillForumsIntoCategories($categories);

		echo $this->render('home', [
			'categories' => $categories
		]);
	}

}
