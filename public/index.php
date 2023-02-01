<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DatabaseAdapterPhp\Factories\PostgreSQLConnectionFactory;

$db = PostgreSQLConnectionFactory::create();

var_dump($db->isConnected());
