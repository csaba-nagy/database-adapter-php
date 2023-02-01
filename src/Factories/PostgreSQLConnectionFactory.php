<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Factories;

use DatabaseAdapterPhp\Abstractions\AbstractDatabaseFactory;
use DatabaseAdapterPhp\Enums\DatabaseDriver;
use DatabaseAdapterPhp\Models\PostgreSQLConnector;

class PostgreSQLConnectionFactory extends AbstractDatabaseFactory
{
  public static function create(?array $values = null): PostgreSQLConnector
  {
    $dsn = PostgreSQLConnector::getDsn(DatabaseDriver::POSTGRESQL, $values);

    return new PostgreSQLConnector($dsn);
  }
}
