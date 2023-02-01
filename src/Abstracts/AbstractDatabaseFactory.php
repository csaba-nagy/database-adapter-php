<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Abstracts;

abstract class AbstractDatabaseFactory
{
  abstract protected static function create(?array $values = null): AbstractDatabaseConnector;
}
