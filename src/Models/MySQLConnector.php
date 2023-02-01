<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Models;

use DatabaseAdapterPhp\Abstracts\AbstractDatabaseConnector;
use DatabaseAdapterPhp\Enums\DatabaseDriver;

class MySQLConnector extends AbstractDatabaseConnector
{
  public static function getDsn(DatabaseDriver $driver, array $values): string
  {
    $format = '%s:host=%s;port=%d;dbname=%s;user=%s;password=%s;charset=%s';

    $values = [
      $values['host'] ?? 'mariadb',
      $values['port'] ?? 3306,
      $values['dbname'] ?? 'mariadb',
      $values['user'] ?? 'mariadb',
      $values['password'] ?? 'mariadb',
      $values['charset'] ?? 'utf8mb4',
    ];

    return sprintf($format, $driver->value, ...$values);
  }
}
