<?php
declare(strict_types=1);

namespace DatabaseAdapterPhp\Exceptions;

use Exception;

class MissingDotEnvVariablesException extends Exception {
  protected $message = 'Dotenv file or some of the required environment variables are missing!';
}
