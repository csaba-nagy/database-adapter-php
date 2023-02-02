# Database Adapter for PHP API Services

>❗DISCLAIMER: Please use this library only for learning purposes for your own good!

## Description
Minimal database adapter library to connect relational databases.
Please note that, this package currently works with **only MySQL and PostgreSQL databases**.

## Installation
1. Clone this repository with: `git clone https://github.com/csaba-nagy/database-adapter-php.git`
2. Open the project in Vscode Dev Container
3. Install dependencies via `composer install`
4. Start local development server: `php -S localhost:8080 public/index.php`

## Usage

### Import a *ConnectionFactory into your project
Once you downloaded (or imported) the library into your project, you have access for the connection factories:

```php
use DatabaseAdapterPhp\Factories\MySQLConnectionFactory;

$db = MySQLConnectionFactory::create();

```

with PostgreSQL:

```php
use DatabaseAdapterPhp\Factories\PostgreSQLConnectionFactory;

$db = PostgreSQLConnectionFactory::create();

```
### Connect to the database with custom data
By default, connection factories are using the following connection data:

**MySQLConnectionFactory**
```  php
'host' => 'mariadb',
'port => 3306,
'dbname => 'mariadb',
'user => 'mariadb',
'password => 'mariadb',
'charset => 'utf8mb4'
```

**PostgreSQLConnectionFactory**
```php
'host => 'postgres',
'port => 5432,
'dbname => 'postgres',
'user => 'postgres',
'password => 'postgres',
'client_encoding => 'utf8'
```

If you want to change these, you can add your custom data as an associative array to the `*ConnectionFactory::create` method:

```php
$connectionData = [
    'user' => 'root',
    'password' => 'verysafepassword'
];

$db = PostgreSQLConnectionFactory::create($connectionData);

```

### Run queries
The best practice to run your queries is using the fetch() method.
This method takes two parameters, a query and an optional array for named parameters.
The fetch() method returns with the fetched data as an array.

```php
$result = $db->fetch($query, $params);
```

### Changing the PDO options
By default, the following PDO options have been set:
```php
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => true,
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
```
To change this, you can specify an associative array as the second parameter to the `*ConnectionFactory::create` method:

```php
$db = PostgreSQLConnectionFactory::create($connectionData, $custompPdoOptions);
```

## Method references

### *ConnectionFactory::create(?array $values, ?array $pdoOptions): *DatabaseConnector
With this method, you can easily connect to a MySQL or a PostgreSQL database.
It takes two associative arrays as optional parameters, the first is for the connection data and the second one is for the PDO options.

### isConnected(): bool
You can check the connection state with this method:

```php
var_dump($db->isConnected());
```

It returns **true** if the connection is established.

### fetch(string $query, ?array $params): ?array
Please check the "Run queries" chapter.

### prepare(string $query, ?array $params): self
It takes two parameters, a query and the additional named parameters as an array. This method is binding the values, casting the data types automatically and chainable with the other methods like execute or fetchAll.

```php
 $query = <<<SQL
        INSERT INTO users (users.name, users.email)
        VALUES (:name, :email)
        SQL;

  $statement = $db->prepare($query, [
    'name' => 'John Doe',
    'email' => 'jd@example.com'
  ]);
```

### execute(): ?self
To run the prepared query, you can use the execute method.
```php
  $db->prepare($query)->execute();
```

### fetchAll(): ?array
It extends the PDO fetchAll() method. It returns with the fetched data as an array.

```php
  $result = $db->prepare($query)->execute()?->fetchAll();

```

### rowCount(): int
It returns the number of the affected rows by the last SQL statement.
```php
  $statement = $db->prepare($query);

  var_dump($statement->execute()->rowCount());

```

If none of the rows are affected, it returns 0.

### getLastInsertedId(?string $name): int
It returns the last created id as an integer.

```php
  var_dump($sdb->lastInsertId());

  ```
