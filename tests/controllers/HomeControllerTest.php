<?php

use Config\Services;
use Tests\Support\Helpers\ControllerTester;

class HomeControllerTest extends CIDatabaseTestCase
{
	use ControllerTester;

	public function setUp()
	{
		parent::setUp();
		Services::reset();
	}

	public function testIndexRedirects()
	{
		$result = $this->withURI('http://example.com/')
			->controller(\App\Controllers\Home::class)
			->execute('index');

		$this->assertTrue($result->isRedirect());
		$this->assertTrue($result->response()->hasHeader('Location'));
	}

}
