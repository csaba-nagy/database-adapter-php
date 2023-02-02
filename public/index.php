<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DatabaseAdapterPhp\Factories\MySQLConnectionFactory;

$db = MySQLConnectionFactory::create();

var_dump($db->isConnected());
