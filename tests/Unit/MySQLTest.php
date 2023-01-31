<?php

declare(strict_types=1);

namespace Tests\Unit;

use DatabaseAdapterPhp\DatabaseConnector;
use DatabaseAdapterPhp\Driver;
use PHPUnit\Framework\TestCase;

class MySQLTest extends TestCase
{
    protected ?DatabaseConnector $db = null;
    protected string $dsn;

    protected function setUp(): void
    {
        $this->dsn = DatabaseConnector::getDsn(Driver::MYSQL);
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
                id int(11) NOT NULL AUTO_INCREMENT,
                weight float(10, 2) DEFAULT NULL,
                name varchar(255) DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL;

        $this->db->prepare($query)->execute();
    }

    public function testShouldBuildDsn(): void
    {
        $expected = 'mysql:host=mariadb;port=3306;dbname=mariadb';
        $expected .= ';user=mariadb;password=mariadb';
        $expected .= ';charset=utf8mb4';

        $this->assertEquals($expected, $this->dsn);

        $expected = 'mysql:host=localhost;port=33060;dbname=test';
        $expected .= ';user=test;password=test';
        $expected .= ';charset=latin2';

        $dsn = DatabaseConnector::getDsn(Driver::MYSQL, [
            'host' => 'localhost',
            'port' => 33060,
            'dbname' => 'test',
            'user' => 'test',
            'password' => 'test',
            'charset' => 'latin2',
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
