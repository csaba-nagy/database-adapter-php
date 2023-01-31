<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

enum Driver: string
{
    case PGSQL = 'pgsql';
    case MYSQL = 'mysql';
}

class DatabaseConnector
{
    public const PDO_OPTIONS = [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    private static ?DatabaseConnector $instance;

    private ?PDO $pdo;
    private ?PDOStatement $statement;

    private function __construct(string $dsn, ?array $options = null)
    {
        $this->pdo = new PDO($dsn, null, null, $options);
    }

    public static function getDsn(Driver $driver, array $values = null): string
    {
        $format = '%s:host=%s;port=%d;dbname=%s;user=%s;password=%s';

        if ($driver === Driver::MYSQL) {
            $format .= ';charset=%s';

            $values = [
                $values['host'] ?? 'mariadb',
                $values['port'] ?? 3306,
                $values['dbname'] ?? 'mariadb',
                $values['user'] ?? 'mariadb',
                $values['password'] ?? 'mariadb',
                $values['charset'] ?? 'utf8mb4',
            ];
        }

        if ($driver === Driver::PGSQL) {
            $format .= ";options=--client-encoding=%s";

            $values = [
                $values['host'] ?? 'postgres',
                $values['port'] ?? 5432,
                $values['dbname'] ?? 'postgres',
                $values['user'] ?? 'postgres',
                $values['password'] ?? 'postgres',
                $values['client_encoding'] ?? 'utf8',
            ];
        }

        return sprintf($format, $driver->value, ...$values);
    }

    public static function getInstance(string $dsn, ?array $options = null): self
    {
        return self::$instance ??= new self($dsn, $options);
    }

    public function isConnected(): bool
    {
        return (bool) $this->pdo;
    }

    public function prepare(string $query, ?array $params = null): self
    {
        $this->statement = $this->pdo?->prepare($query) ?: null;

        return empty($params) ? $this : $this->bindValues($params);
    }

    public function execute(): ?self
    {
        return $this->statement?->execute() ? $this : null;
    }

    public function rowCount(): int
    {
        return $this->statement?->rowCount() ?? 0;
    }

    public function lastInsertId(?string $name = null): int
    {
        return (int) $this->pdo?->lastInsertId($name);
    }

    public function fetchAll(): ?array
    {
        if (empty($this->execute()?->rowCount())) {
            return null;
        }

        return $this->statement?->fetchAll() ?: null;
    }

    public function fetch(string $query, ?array $params = null): ?array
    {
        return $this->prepare($query, $params)->execute()?->fetchAll();
    }

    public function debug(): string
    {
        // Using a trait or a closure will be more accurate.
        if (ob_start()) {
            $this->statement->debugDumpParams();

            return (string) ob_get_clean();
        }

        return '';
    }

    private function bindValues(array $params): self
    {
        foreach ($params as $key => $value) {
            $args = [":{$key}", $value, $this->getValueType($value)];

            if ($this->statement?->bindValue(...$args)) {
                continue;
            }

            throw new InvalidArgumentException('Cannot bind value');
        }

        return $this;
    }

    private function getValueType(mixed $value): int
    {
        return match (true) {
            is_null($value) => PDO::PARAM_NULL,
            is_bool($value) => PDO::PARAM_BOOL,
            is_numeric($value) => PDO::PARAM_INT,
            default => PDO::PARAM_STR,
        };
    }
}
