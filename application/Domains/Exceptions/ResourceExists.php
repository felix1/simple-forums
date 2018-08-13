<?php namespace App\Exceptions;

use Exception;

class ResourceExists extends Exception
{
	protected $code = 409;
}
