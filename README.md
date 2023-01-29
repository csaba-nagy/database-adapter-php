# Database Adapter for PHP API Services

### DISCLAIMER: Learning purposes only!

## Before you start:
This package is using environment variables, once you import this package, be sure the `.env` file is exists in your application.
The required environment variables are included in the `.env.example` file or the list below.
The `.env` file can be copied with the `cp .env.example .env` command.

***Required environment variables:***
- DB_DRIVER
- DB_HOST
- DB_PORT
- DB_NAME
- DB_USERNAME
- DB_PASSWORD
- DB_CHARSET

## Basic Usage:

### Load
The core of this package is the **DatabaseConnector** class. You can load this class with:
```
use DatabaseAdapterPhp\DatabaseConnector;

$db = DatabaseConnector::getInstance();

```

This class provide some useful methods, which are help you to handle database connections easily.

## Method references

### isConnected(): bool

You can check the connection state with this method:

```
var_dump($db->isConnected());

```

It returns ***true*** if the connection is established.

### prepare(string $query, ?array $params): self

It's a wrapper/proxy method for the PDO prepare method. It takes two parameters, a query(string), and the additional named parameters as an array(it's optional, but recommended). This method is bind the values, cast the data types automatically and chainable with the other methods like **execute** or **fetchAll**.

```
 $query = <<<SQL
        INSERT INTO users (users.name, users.email)
        VALUES (:name, :email)
        SQL;

  $statement = $db->prepare($query, [
    'name' => 'John Doe',
    'email' => 'jd@example.com'
  ]);
```

### execute(): self
To run the prepared query, you can use the `execute` method.

```
  $statement->execute();
```

This method is a wrapper method for the PDO execute method, which returns ***true*** after successfully finished. Based on that, this execute method returns ***self*** after a successful run, otherwise throws a PDOException.


### fetchAll(): ?array
It extends the PDO fetchALL() method. If the affected rows by the prepared statement is null, it returns ***null***, otherwise returned the fetched data as an ***array***.

```
  $statement = $db->prepare($query);

  var_dump($statement->fetchAll());

```

### rowCount(): int
It returns the number of the affected rows by the last SQL statement.

```
  $statement = $db->prepare($query);

  var_dump($statement->execute()->rowCount());

```
If none of the rows are affected, it returns ***-1***.

### lastInsertId(): int
It returns the last created id as an integer. If the `lastInsertId` method cannot return the `id`, it throws a PDOException.

```
  var_dump($sdb->lastInsertId());

```

## Credit
All credit to [@nandordudas](https://github.com/nandordudas)
