<?php

declare(strict_types=1);

namespace DatabaseAdapterPhp\Contracts;

interface Connectable
{
  public function isConnected(): bool;
  public function prepare(string $query, ?array $params = null): self;
  public function execute(): ?self;
  public function rowCount(): int;
  public function fetchAll(): ?array;
  public function fetch(string $query, ?array $params = null): ?array;
  public function getLastInsertedId(?string $name = null): int;
}
