<?php namespace App\Exceptions;

use Exception;

class ResourceNotFound extends Exception
{
	protected $code = 404;
}
