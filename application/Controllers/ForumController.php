<?php namespace App\Controllers;

use App\Domains\Forums\ForumModel;
use App\Domains\Forums\ThreadModel;
use App\Domains\Posts\PostModel;
use Config\Services;

class ForumController extends BaseController
{
	/**
	 * @var \App\Domains\Forums\ForumModel
	 */
	protected $forums;

	/**
	 * @var \App\Domains\Forums\ThreadModel
	 */
	protected $threads;

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->forums = new ForumModel();
		$this->threads = new ThreadModel();
	}

	/**
	 * Displays the forums in a block/category view.
	 */
	public function showCategories()
	{
		helper('typography');

		$categories = $this->forums->findCategories();
		$categories = $this->forums->fillForumsIntoCategories($categories);

		echo $this->render('forums/categories', [
			'categories' => $categories,
			'formatter'  => Services::typography()
		]);
	}

	/**
	 * Displays the forums as recent discussions view.
	 */
	public function showRecent()
	{
		echo $this->render('forums/recent', [
			'threads'       => $this->threads->paginate(20),
			'pager'         => $this->threads->pager,
			'totalThreads'  => $this->threads->totalThreads(),
		]);
	}

	/**
	 * Displays the overview of a single forum.
	 *
	 * @param int $id
	 */
	public function showForum(string $slug)
	{
		$id = (int)$slug;
		$forum = $this->forums->find($id);

		$threads = $this->threads->findForForum($id);
		$threads = $this->threads->fillUsers($threads);

		echo $this->render('forums/show', [
			'forum'     => $forum,
			'threads'   => $threads,
			'pager'     => $this->threads->pager,
		]);
	}

}
