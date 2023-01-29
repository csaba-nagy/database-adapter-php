<?php
declare(strict_types=1);

namespace DatabaseAdapterPhp;

require __DIR__ . '/../vendor/autoload.php';

use DatabaseAdapterPhp\Exceptions\MissingDotEnvVariablesException;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use Dotenv\Dotenv;

define('ROOT_PATH', __DIR__ . '/../');

class DatabaseConnector
{
  private static ?DatabaseConnector $instance;

  private ?PDO $pdo = null;
  private ?PDOStatement $statement = null;

  private const PDO_OPTIONS = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => true
  ];

  /**
   *
   * @return void
   * @throws \PDOException
   */
  private function __construct()
  {
    try {
      $dotenv = Dotenv::createImmutable(ROOT_PATH);

      $dotenv->load();

      $dotenv->required([
        'DB_DRIVER',
        'DB_HOST',
        'DB_PORT',
        'DB_NAME',
        'DB_USERNAME',
        'DB_PASSWORD',
        'DB_CHARSET'
      ]);
    } catch (\Throwable $th) {
      throw new MissingDotEnvVariablesException($th->getMessage());
    }

    $this->pdo = new PDO($this->getDsn(), $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], self::PDO_OPTIONS);
  }

  /**
   *
   * @return \DatabaseAdapterPhp\DatabaseConnector
   * @throws \PDOException
   */
  public static function getInstance(): self
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Create DSN. Example: `pgsql:host=localhost:5432;dbname=database;charset=utf8mb4`
   */
  private function getDsn(): string
  {
    return sprintf(
      '%s:host=%s:%d;dbname=%s;charset=%s',
      ...[$_ENV['DB_DRIVER'], $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME'], $_ENV['DB_CHARSET']],
    );
  }

  /**
   * It returns true if the PDO connection is set
   */
  public function isConnected(): bool
  {
    return isset($this->pdo);
  }

  /**
   * Proxy method for the PDO `prepare method`. It takes two params,
   * a query as a string, and the additional named parameters as an array *(it is optional)*.
   * It returns `self`, so we can chain the methods.
   *
   * @param string $query
   * @param array $params
   * @return \DatabaseAdapterPhp\DatabaseConnector
   */
  public function prepare(string $query, ?array $params = null): self
  {
    $stmt = $this->pdo?->prepare($query);

    if (!$stmt) {
      return $this;
    }

    $this->statement = $stmt;

    return $params ? $this->bindValues($params) : $this;
  }

  /**
   * Proxy method **for the PDO execute method**.
   * The standard PDO execute method returns a boolean after the execute command finished,
   * if it is true the proxy method returns with `self`,
   * else throw an exception.
   *
   * @return \DatabaseAdapterPhp\DatabaseConnector
   * @throws \PDOException
   */
  public function execute(): self
  {
    return $this->statement?->execute()
      ? $this
      : throw new PDOException('Statement cannot be executed!');
  }

  /**
   * Proxy method which use the **PDO rowCount method**.
   * It returns the number **(int)** of rows affected by the last SQL statement
   *
   * @return int
   * @throws \PDOException
   */
  public function rowCount(): int
  {
    return $this->statement ? $this->statement->rowCount() : -1;
  }

  /**
   * Proxy method which extends the **PDO lastInsertId method:**
   * If the `lastInsertId` method cannot return the `id`, it throws an exception.
   *
   * @return int
   * @throws \PDOException
   */
  public function lastInsertId(): int
  {
    return (int) $this->pdo?->lastInsertId()
      ?: throw new PDOException('Cannot get last inserted id');
  }

  /**
   * Proxy method which use and extends the **PDO fetchAll method** :
   * If the the affected rows *(by SQL statement)* is null *(it returns from the `rowCount()` method)*,
   * this method will return with null, else it runs the PDO fetchAll method on the statement.
   * If the return value is false, it returns with null, else the fetched data will be returned.
   */
  public function fetchAll(): ?array
  {
    if ($this->execute()->rowCount()) {
      $result = $this->statement?->fetchAll();

      //the fetchAll() method returns `false` on failure
      if (!$result) {
        return null;
      }

      return $result;
    }

    return null;
  }

  /**
   * Helper method for the **prepare proxy method** (see above)
   * Iterate through an array, and bind the named parameters for the prepare method
   * It returns `self`, because of the return value of the prepare method must be `self`.
   * the $params should be signed this way: ['name' => $name, 'age' => $age]
   *
   * @param array $params
   * @return \DatabaseAdapterPhp\DatabaseConnector
   * @throws \InvalidArgumentException
   */
  private function bindValues(array $params): self
  {
    if (!$params) {
      return $this;
    }

    foreach ($params as $key => $value) {
      if ($this->statement?->bindValue(":{$key}", ...$this->castDataType($value))) {
        continue;
      }

      throw new InvalidArgumentException('Cannot bind value');
    }

    return $this;
  }

  /**
   * Helper function for the **bindValues method**.
   * It returns an array which contains the exact type of the given value.
   * *It use the `match` expression, which is very similar with the `switch` statement*
   */
  private function castDataType(mixed $value): array
  {
    return [
      $value,
      match (true) {
        is_null($value) => PDO::PARAM_NULL,
        is_bool($value) => PDO::PARAM_BOOL,
        is_numeric($value) => PDO::PARAM_INT,
        default => PDO::PARAM_STR,
      }
    ];
  }
}
