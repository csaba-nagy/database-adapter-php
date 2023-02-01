<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Models;

use DatabaseAdapterPhp\Abstracts\AbstractDatabaseConnector;
use DatabaseAdapterPhp\Enums\DatabaseDriver;

class PostgreSQLConnector extends AbstractDatabaseConnector
{
  public static function getDsn(DatabaseDriver $driver, array $values = null): string
  {
    $format = '%s:host=%s;port=%d;dbname=%s;user=%s;password=%s;options=--client-encoding=%s';

    $values = [
      $values['host'] ?? 'postgres',
      $values['port'] ?? 5432,
      $values['dbname'] ?? 'postgres',
      $values['user'] ?? 'postgres',
      $values['password'] ?? 'postgres',
      $values['client_encoding'] ?? 'utf8',
    ];

    return sprintf($format, $driver->value, ...$values);
  }
}
