<?php namespace App\Exceptions;

use Exception;

class ResourceDeleted extends Exception
{
	protected $code = 404;
}
