<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Enums;

enum DatabaseDriver: string
{
  case MYSQL = 'mysql';
  case POSTGRESQL = 'pgsql';
}
