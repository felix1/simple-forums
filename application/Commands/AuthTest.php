<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AuthTest extends BaseCommand
{
  protected $group = 'auth';

  /**
   * The Command's name
   *
   * @var string
   */
  protected $name = 'auth:test';

  /**
   * the Command's short description
   *
   * @var string
   */
  protected $description = 'Determines the appropriate password_hash cost.';

  public function run(array $params=[])
  {
      $targetTime = $params['time'] ?? null;

      if (empty($targetTime))
      {
          $targetTime = CLI::prompt('Target Time (ms)', '50');
      }

	  // Convert the milliseconds to seconds.
	  $targetTime = $targetTime/1000;

	  CLI::write('Testing for password hash value with a target time of '.$targetTime.' seconds...');

	  // Taken from the PHP manual
	  $cost = 8;
	  do {
		  $cost++;
		  $start = microtime(true);
		  password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
		  $end = microtime(true);
	  } while (($end - $start) < $targetTime);

	  CLI::write("Hash value should be set to: ". CLI::color($cost, 'green'));
  }

  //--------------------------------------------------------------------
}
