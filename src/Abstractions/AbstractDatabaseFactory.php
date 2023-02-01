<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Abstractions;

abstract class AbstractDatabaseFactory
{
    abstract protected static function create(?array $values = null): AbstractDatabaseConnector;
}
