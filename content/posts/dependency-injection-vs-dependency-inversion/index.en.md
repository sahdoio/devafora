---
title: 'Dependency Injection vs Dependency Inversion'
excerpt: 'Dependency Injection (DI) and Dependency Inversion (DIP) are often confused. Learn how they work together to boost flexibility, maintainability, and scalability in your code.'
author: Lucas Sahdo
image: capa.webp
tags:
  - php
  - oop
  - solid
  - dependency-injection
published_at: '2025-02-26'
draft: false
newsletter_sent_at: ~
---

Dependency Injection (DI) and Dependency Inversion (DIP) are two key concepts in software development that often get confused. While they're related, they focus on different things. Dependency Injection deals with how objects get their dependencies, making systems easier to test and more modular. Dependency Inversion, part of the SOLID principles, focuses on ensuring high-level code depends on abstractions, not concrete implementations.

In this article, we'll break down how these ideas work together to boost flexibility, maintainability, and scalability in your code.

## Dependency Injection

The ability to inject dependencies through the constructor. According to Laravel's documentation on the service container:

> "Dependency injection is a fancy phrase that essentially means this: class dependencies are 'injected' into the class via the constructor or, in some cases, 'setter' methods."

For example, imagine that you have a `UserService` and you want to connect to the database through a `Database` class. Here's a class without using DI:

```php
<?php

class UserService
{
    private MySQLDatabase $database;

    public function __construct() {
        // Warning: Creating a new instance inside the class !!!
        $this->database = new MySQLDatabase();
    }

    public function getUser($id): string {
        return $this->database->query("SELECT * FROM users WHERE id = $id");
    }
}

class MySQLDatabase
{
    public function query($sql): string {
        return "User data for ID: $sql";
    }
}

$userService = new UserService();
echo $userService->getUser(1);
```

This defines an **Association** in Object-Oriented Programming (OOP). Specifically, it is a **Composition Association** since the `UserService` class holds an instance of the `MySQLDatabase` class and controls its lifecycle.

> Composition is a strong form of association. In it, the "container" (or "whole") class **owns** the instance of the "part" class.

Now let's rewrite the class applying DI (Dependency Injection):

```php
<?php

class UserService
{
    public function __construct(private MySQLDatabase $database) {}

    public function getUser($id): string {
        return $this->database->query("SELECT * FROM users WHERE id = $id");
    }
}

class MySQLDatabase
{
    public function query($sql): string {
        return "User data for ID: $sql";
    }
}

// dependency from outside
$mysqlDatabase = new MySQLDatabase();
// user service receives dependency from outside
$userService = new UserService($mysqlDatabase);

echo $userService->getUser(1);
```

Using **Dependency Injection (DI)** in this example improves flexibility, testability, and maintainability, but it does not fully decouple the system on its own. While DI allows the database dependency to be passed from the outside instead of being instantiated within `UserService`, the class is still tied to a specific implementation (`MySQLDatabase`). This means **DI alone does not eliminate dependency on concrete implementations**, it just makes injecting dependencies more manageable.

## The Coupling Problem: DIP Can Handle It

Although `UserService` now receives the dependency from the outside, it is still tightly coupled to `MySQLDatabase` because the dependency type is explicitly defined as `MySQLDatabase`. If we needed to switch to `PostgreSQLDatabase` or `MongoDBDatabase`, we would still have to modify `UserService`, which violates the **Open/Closed Principle (OCP)**. This is where the **Dependency Inversion Principle (DIP)** comes in, promoting the use of **interfaces** instead of concrete implementations.

According to Robert C. Martin (Uncle Bob):

> "Depend in the direction of abstraction. High level modules should not depend upon low level details."

In our example, by depending on an interface (`DatabaseInterface`), `UserService` becomes agnostic to the actual database implementation. This allows us to inject any database class that implements `DatabaseInterface`, enabling true flexibility and reducing coupling.

Here's the refactored version following **DIP**:

```php
<?php

interface DatabaseInterface
{
    public function query(string $sql): string;
}

class MySQLDatabase implements DatabaseInterface
{
    public function query(string $sql): string
    {
        return "User data from MySQL for ID: $sql";
    }
}

class PostgreSQLDatabase implements DatabaseInterface
{
    public function query(string $sql): string
    {
        return "User data from PostgreSQL for ID: $sql";
    }
}

class UserService
{
    private DatabaseInterface $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getUser(int $id): string
    {
        return $this->database->query("SELECT * FROM users WHERE id = $id");
    }
}

// Injecting MySQLDatabase
$mysqlDatabase = new MySQLDatabase();
$userServiceMySQL = new UserService($mysqlDatabase);
echo $userServiceMySQL->getUser(1);

// Injecting PostgreSQLDatabase
$postgresDatabase = new PostgreSQLDatabase();
$userServicePostgres = new UserService($postgresDatabase);
echo $userServicePostgres->getUser(2);
```

Now `UserService` is completely agnostic to the database implementation. It doesn't need to know any details about it, only how to use it through the interface.

The **Dependency Inversion Principle (DIP)** makes decoupling ideal because `UserService` no longer needs to know about the specific database implementation it interacts with. Instead of depending on a concrete class like `MySQLDatabase`, it relies on an abstraction (`DatabaseInterface`), allowing any database implementation to be injected without modifying `UserService`.

This makes future changes seamless. If a different database like `MongoDBDatabase` or `PostgreSQLDatabase` is needed, it can be integrated simply by implementing the interface. As a result, the system becomes more flexible, maintainable, and aligned with **SOLID principles**, particularly **Open/Closed (OCP)** and **Dependency Inversion (DIP)**.

## A Good Use Case: The Service Layer / Repository Pattern

The examples above are simple. Service layers, in a Layered Architecture, should not access DB implementations through database classes directly. Using a Repository might be better here since it acts as an abstraction over database operations, separating business logic from data access.

This makes it easier to switch database implementations without modifying service logic, improving maintainability, scalability, and testability. Here is an example using all the concepts explained until now and adding the repository layer.

The structure would be:

![Architecture diagram showing the layered structure](diagrama-arquitetura.png)

> ⚠️ **Note:** This is a very basic example using pure PHP and PDO. It is only an example for reference. In a real-world application, you will have more tools provided by frameworks like Laravel or Symfony to handle dependency injection, database abstraction, and other advanced features.

### `/app/Database/DatabaseInterface.php`

```php
<?php

declare(strict_types=1);

namespace App\Database;

interface DatabaseInterface
{
    public function query(string $sql, array $params = []): array;
}
```

### `/app/Database/MySQLDatabase.php`

```php
<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;
use App\Database\DatabaseInterface;

class MySQLDatabase implements DatabaseInterface
{
    private PDO $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=app", "user", "password");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database connection failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database query failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }
}
```

### `/app/Database/PostgreSQLDatabase.php`

```php
<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;
use App\Database\DatabaseInterface;

class PostgreSQLDatabase implements DatabaseInterface
{
    private PDO $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO("pgsql:host=localhost;dbname=app", "user", "password");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database connection failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database query failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }
}
```

### `/app/Repository/UserRepositoryInterface.php`

```php
<?php

declare(strict_types=1);

namespace App\Repository;

interface UserRepositoryInterface
{
    public function getUserById(int $id): ?array;
}
```

### `/app/Repository/DbUserRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\DatabaseInterface;
use App\Repository\UserRepositoryInterface;

class DbUserRepository implements UserRepositoryInterface
{
    public function __construct(private DatabaseInterface $database) {}

    public function getUserById(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        return $this->database->query($sql, ['id' => $id]);
    }
}
```

### `/app/Repository/InMemoryUserRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Repository\UserRepositoryInterface;

class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [
        1 => ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
        2 => ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
    ];

    public function getUserById(int $id): ?array
    {
        return $this->users[$id] ?? null;
    }
}
```

### `/app/Service/UserService.php`

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepositoryInterface;

class UserService
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function getUserDetails(int $id): ?array
    {
        $user = $this->userRepository->getUserById($id);
        if (empty($user)) {
            return null;
        }
        $user['email'] = substr($user['email'], 0, 3) . '****@' . explode('@', $user['email'])[1];
        return $user;
    }
}
```

This `UserService` might look like **just a proxy** because it simply fetches user details, making it seem redundant. However, in real-world scenarios, **service layers handle way more than just CRUD operations**.

In complex applications, the service layer is responsible for **business rules, validations, aggregating data from multiple sources, handling transactions, caching, and orchestrating different dependencies**. The example here is intentionally simple to demonstrate the structure flow, making it easier to understand in the context of an article.

While in this case it only retrieves a user and masks their email, in production systems services could be responsible for permission checks, event dispatching, sending notifications, or applying domain logic before returning data. So even though it looks unnecessary now, service layers become crucial as business complexity grows.

### `/public/index.php`

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database\MySQLDatabase;
use App\Database\PostgreSQLDatabase;
use App\Repository\DbUserRepository;
use App\Repository\InMemoryUserRepository;
use App\Service\UserService;

$mysqlDatabase = new MySQLDatabase();
$userRepository = new DbUserRepository($mysqlDatabase);
$userService = new UserService($userRepository);
print_r($userService->getUserDetails(1));

$postgresDatabase = new PostgreSQLDatabase();
$userRepositoryPostgres = new DbUserRepository($postgresDatabase);
$userServicePostgres = new UserService($userRepositoryPostgres);
print_r($userServicePostgres->getUserDetails(2));

$inMemoryRepo = new InMemoryUserRepository();
$userServiceMemory = new UserService($inMemoryRepo);
print_r($userServiceMemory->getUserDetails(1));
```

In traditional frameworks like Laravel, Symfony, or Slim, we typically wouldn't manually instantiate dependencies like in this example. Instead, we would leverage the **Dependency Injection (DI) container** to handle object resolution automatically.

## Final Thoughts

The idea is simple: reduce dependency between components. DI handles how dependencies are provided, while DIP ensures code relies on abstractions, not implementations. Together, they keep things modular and adaptable.

They also tie into Agile values, like adaptability and incremental development, helping teams build systems that evolve easily with changing needs.

In short, DI and DIP work hand-in-hand, laying the groundwork for strong, scalable, and modern software designs.

## References

- **SOLID Principles** — <https://blog.cleancoder.com/uncle-bob/2020/10/18/Solid-Relevance.html>
- **Laravel Dependency Injection Container** — <https://laravel.com/docs/container>
- **Symfony Dependency Injection Component** — <https://symfony.com/doc/current/components/dependency_injection.html>
- **Slim Framework Dependency Injection** — <https://www.slimframework.com/docs/v4/concepts/di.html>
