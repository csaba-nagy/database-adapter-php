<?php

declare(strict_types=1);

namespace Tests\Unit;

use DatabaseAdapterPhp\DatabaseConnector;
use DatabaseAdapterPhp\Driver;
use PHPUnit\Framework\TestCase;

class PgSQLTest extends TestCase
{
    protected ?DatabaseConnector $db = null;
    protected string $dsn;

    protected function setUp(): void
    {
        $this->dsn = DatabaseConnector::getDsn(Driver::PGSQL);
        $this->db = DatabaseConnector::getInstance($this->dsn);

        $this->createTestTable();
    }

    protected function tearDown(): void
    {
        $query = <<<SQL
            DROP TABLE IF EXISTS test
        SQL;

        $this->db->prepare($query)->execute();

        $this->db = null;
    }

    protected function createTestTable()
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS test (
                id int NOT NULL PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
                weight FLOAT,
                name VARCHAR(255)
            )
        SQL;

        $this->db->prepare($query)->execute();
    }

    public function testShouldBuildDsn(): void
    {
        $expected = 'pgsql:host=postgres;port=5432;dbname=postgres';
        $expected .= ';user=postgres;password=postgres';
        $expected .= ';options=--client-encoding=utf8';

        $this->assertEquals($expected, $this->dsn);

        $expected = 'pgsql:host=localhost;port=54320;dbname=test';
        $expected .= ';user=test;password=test';
        $expected .= ';options=--client-encoding=latin2';

        $dsn = DatabaseConnector::getDsn(Driver::PGSQL, [
            'host' => 'localhost',
            'port' => 54320,
            'dbname' => 'test',
            'user' => 'test',
            'password' => 'test',
            'client_encoding' => 'latin2',
        ]);

        $this->assertEquals($expected, $dsn);
    }

    public function testShouldCreateSingleton(): void
    {
        $instance = DatabaseConnector::getInstance($this->dsn);

        $this->assertInstanceOf(DatabaseConnector::class, $this->db);
        $this->assertEquals($instance, $this->db);
    }

    public function testShouldCheckConnectionIsAlive(): void
    {
        $this->assertTrue($this->db->isConnected());
    }

    public function testShouldPrepareQuery(): void
    {
        $query = <<<SQL
            SELECT
                test.name,
                test.weight
            FROM
                test
            WHERE 1=1
                AND test.name = :name
        SQL;

        $this->assertStringContainsString(
            'Params:  0',
            $this->db->prepare('SELECT 1 FROM test')->debug(),
        );

        // Check bindValues and castDataType methods.
        $this->assertStringContainsString(
            'param_type=2',
            $this->db->prepare($query, ['name' => 'John'])->debug(),
        );
    }

    public function testShouldExecutePreparedQuery(): void
    {
        $query = <<<SQL
            SELECT
                test.name,
                test.weight
            FROM
                test
            WHERE 1=1
                AND test.name = :name
        SQL;

        $this->db
            ->prepare(<<<SQL
                INSERT INTO test (
                    weight,
                    name
                ) VALUES (
                    63.245,
                    'John'
                )
            SQL)
            ?->execute();

        $result = $this->db
            ->prepare($query, ['name' => 'John'])
            ?->execute();

        $this->assertInstanceOf(DatabaseConnector::class, $result);
    }

    public function testShouldFetchData(): void
    {
        $query = <<<SQL
            SELECT
                test.name,
                test.weight
            FROM
                test
            WHERE 1=1
                AND test.name = :name
        SQL;

        $this->db
            ->prepare(<<<SQL
                INSERT INTO test (
                    weight,
                    name
                ) VALUES (
                    63.245,
                    'John'
                )
            SQL)
            ->execute();

        [$result] = $this->db
            ->prepare($query, ['name' => 'John'])
            ->execute()
            ?->fetchAll();

        $this->assertContains('John', $result);
    }

    public function testShouldFetchDataQuickly(): void
    {
        $query = <<<SQL
            SELECT
                test.name,
                test.weight
            FROM
                test
            WHERE 1=1
                AND test.name = :name
        SQL;

        $this->db
            ->prepare(<<<SQL
                INSERT INTO test (
                    weight,
                    name
                ) VALUES (
                    63.245,
                    'John'
                )
            SQL)
            ->execute();

        [$result] = $this->db->fetch($query, ['name' => 'John']);

        $this->assertContains('John', $result);
    }

    public function testShouldReturnAffectedRowCount(): void
    {
        $result = $this->db
            ->prepare(<<<SQL
                INSERT INTO test (
                    weight,
                    name
                ) VALUES (
                    63.245,
                    'John'
                )
            SQL)
            ->execute()
            ?->rowCount();

        $this->assertEquals(1, $result);
    }

    public function testShouldReturnLastInsertId(): void
    {
        $result = $this->db
            ->prepare(<<<SQL
                INSERT INTO test (
                    weight,
                    name
                ) VALUES (
                    63.245,
                    'John'
                )
            SQL)
            ->execute()
            ?->lastInsertId();

        $this->assertEquals(1, $result);
    }
}
