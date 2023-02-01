<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Abstractions;

use DatabaseAdapterPhp\Contracts\Connectable;
use DatabaseAdapterPhp\Enums\DatabaseDriver;
use InvalidArgumentException;
use PDO;
use PDOStatement;

abstract class AbstractDatabaseConnector implements Connectable
{
    protected ?PDO $pdo = null;
    protected ?PDOStatement $statement = null;

    protected const PDO_OPTIONS = [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];

    public function __construct(string $dsn, ?array $options = null)
    {
        $this->pdo = new PDO($dsn, null, null, $options ?? $this::PDO_OPTIONS);
    }

    abstract public static function getDsn(DatabaseDriver $driver, array $values);

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

    public function getLastInsertedId(?string $name = null): int
    {
        return (int) $this->pdo?->lastInsertId($name);
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
