<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Factories;

use DatabaseAdapterPhp\Abstractions\AbstractDatabaseFactory;
use DatabaseAdapterPhp\Enums\DatabaseDriver;
use DatabaseAdapterPhp\Models\MySQLConnector;

class MySQLConnectionFactory extends AbstractDatabaseFactory
{
    public static function create(?array $values = null, ?array $pdoOptions = null): MySQLConnector
  {
    $dsn = MySQLConnector::getDsn(DatabaseDriver::MYSQL, $values);

        return new MySQLConnector($dsn, $pdoOptions);
  }
}
