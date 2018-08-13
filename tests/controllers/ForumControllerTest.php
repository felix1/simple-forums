<?php

use Config\Services;
use Tests\Support\Helpers\ControllerTester;

class ForumControllerTest extends CIDatabaseTestCase
{
	use ControllerTester;

	public function setUp()
	{
		parent::setUp();
		Services::reset();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidURL()
	{
		$this->withURI('http://example.com/foo/bar')
			->controller(\App\Controllers\ForumController::class)
			->execute('notIt');
	}

	public function testShowCategories()
	{
		$result = $this->withURI('http://example.com/categories')
			->controller(\App\Controllers\ForumController::class)
			->execute('showCategories');

		$this->assertTrue($result->isOK());

		$this->assertTrue($result->see('Some Forums'));
	}



}
