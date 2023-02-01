<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Exceptions;

use Exception;

class NotImplementedMethodException extends Exception
{
  protected $message = 'Not implemented method.';
}
