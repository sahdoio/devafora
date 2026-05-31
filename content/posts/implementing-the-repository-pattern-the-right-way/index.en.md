---
title: 'Implementing the Repository Pattern — The Right Way'
excerpt: 'Repositories are not "just proxies for ORMs". A deep dive into Domain Models, Aggregates, DAO vs Repository, and how to implement the Repository pattern the right way — and when to use it.'
author: Lucas Sahdo
image: capa.webp
tags:
  - php
  - ddd
  - repository
  - laravel
  - architecture
published_at: '2026-01-30'
draft: false
newsletter_sent_at: ~
---

Have you ever encountered someone saying: "Repositories are useless! They're just proxy layers for ORMs"? If you've heard this, know that this person probably never truly understood what this design pattern is for.

In this article, we'll explain end-to-end the purpose of the Repository pattern, the wrong and right ways to implement it, and most importantly: when to use it.

But before we dive into repositories, we need to understand what a Domain Model is, otherwise the pattern explanation won't make much sense. So, have you heard about Domain Models?

## Domain Models

> "A model is a simplified representation of a thing or phenomenon that intentionally emphasizes certain aspects while ignoring others."

The Domain Model is the heart of software in Domain-Driven Design. It represents not just data, but also behaviors and business rules of the domain we're modeling. As Eric Evans describes:

> "The heart of software is its ability to solve domain-related problems for its user. All other features, vital though they may be, support this basic purpose."

A Domain Model is composed of several building blocks. Let's understand the main ones:

### Entities

An **Entity** is an object that has a unique identity that persists over time, even when its attributes change. Identity is what defines an Entity, not its values.

Think of it this way: you can change your address, phone number, even your name, but you're still you. Your identity (your social security number, for example) remains the same.

```php
<?php

namespace App\Domain\Entities;

class Customer
{
    public readonly CustomerId $id;
    public readonly \DateTimeImmutable $registeredAt;

    private(set) string $name;
    private(set) Email $email;
    private(set) Address $address;
    private(set) CustomerStatus $status;
    private int $failedPaymentAttempts = 0;

    private array $domainEvents = [];

    public static function register(
        CustomerId $id,
        string $name,
        Email $email,
        Address $address
    ): self {
        $customer = new self($id, $name, $email, $address);

        $customer->recordEvent(new CustomerRegistered($id, $email));

        return $customer;
    }

    public function changeEmail(Email $newEmail): void
    {
        $this->ensureIsActive();

        $oldEmail = $this->email;
        $this->email = $newEmail;

        $this->recordEvent(new CustomerEmailChanged($this->id, $oldEmail, $newEmail));
    }

    public function recordFailedPayment(): void
    {
        $this->failedPaymentAttempts++;

        if ($this->failedPaymentAttempts >= 3) {
            $this->suspend('Too many failed payment attempts');
        }
    }

    public function suspend(string $reason): void
    {
        if ($this->status->isSuspended()) {
            throw new \DomainException('Customer is already suspended');
        }

        $this->status = CustomerStatus::suspended();

        $this->recordEvent(new CustomerSuspended($this->id, $reason));
    }

    public function reactivate(): void
    {
        if (!$this->status->isSuspended()) {
            throw new \DomainException('Customer is not suspended');
        }

        $this->status = CustomerStatus::active();
        $this->failedPaymentAttempts = 0;

        $this->recordEvent(new CustomerReactivated($this->id));
    }

    private function ensureIsActive(): void
    {
        if ($this->status->isSuspended()) {
            throw new \DomainException('Cannot perform this action on a suspended customer');
        }
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
```

Notice that `Customer` can change address and email, but the `CustomerId` remains the same. Two customers with the same name and email, but different IDs, are **different** customers.

### Value Objects

Unlike Entities, **Value Objects** are defined by their attributes, not by an identity. They are immutable and compared by value.

Think about money: a $100 bill is equal to any other $100 bill. It doesn't matter which specific bill you have, what matters is the value.

```php
<?php

namespace App\Domain\ValueObjects;

class Money
{
    public readonly float $amount;
    public readonly string $currency;

    public function __construct(float $amount, string $currency)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function add(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add different currencies');
        }

        return new Money($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot subtract different currencies');
        }

        return new Money($this->amount - $other->amount, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}
```

Another classic example is `Address`:

```php
<?php

namespace App\Domain\ValueObjects;

class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zipCode
    ) {}

    public function equals(Address $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->state === $other->state
            && $this->zipCode === $other->zipCode;
    }
}
```

Value Objects are immutable. If you need to "change" a Value Object, you create a new one.

### Aggregates

Here we arrive at the most important concept for understanding Repositories correctly.

> "An Aggregate is a cluster of domain objects that we treat as a unit for the purpose of data changes."

An Aggregate defines a consistency boundary. It groups Entities and Value Objects that need to be modified together to keep business rules consistent.

Every Aggregate has an **Aggregate Root**: the main Entity through which all external access must pass. External objects can only reference the Aggregate Root, never the internal entities directly.

Vaughn Vernon emphasizes:

> "Prefer references to external Aggregates only by their globally unique identity, not by holding a direct object reference."

### Practical Example: Order

Let's use a classic e-commerce example: an order with its items.

```php
<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\OrderId;
use App\Domain\ValueObjects\CustomerId;

// Aggregate Root
class Order
{
    public readonly OrderId $id;
    public readonly CustomerId $customerId;
    public readonly \DateTimeImmutable $createdAt;

    private(set) string $status;
    private(set) Money $totalAmount;

    /** @var OrderItem[] */
    private(set) array $items = [];

    public function __construct(OrderId $id, CustomerId $customerId)
    {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->status = 'pending';
        $this->totalAmount = new Money(0, 'USD');
        $this->createdAt = new \DateTimeImmutable();
    }

    public function addItem(
        string $productId,
        string $productName,
        Money $unitPrice,
        int $quantity
    ): void {
        $this->ensurePending();

        $this->items[] = new OrderItem(
            productId: $productId,
            productName: $productName,
            unitPrice: $unitPrice,
            quantity: $quantity
        );

        $this->recalculateTotal();
    }

    public function removeItem(string $productId): void
    {
        $this->ensurePending();

        $this->items = array_values(array_filter(
            $this->items,
            fn(OrderItem $item) => $item->productId !== $productId
        ));

        $this->recalculateTotal();
    }

    public function confirm(): void
    {
        if (empty($this->items)) {
            throw new \DomainException('Cannot confirm an empty order');
        }

        $this->status = 'confirmed';
    }

    private function ensurePending(): void
    {
        if ($this->status !== 'pending') {
            throw new \DomainException('Cannot modify a confirmed order');
        }
    }

    private function recalculateTotal(): void
    {
        $total = new Money(0, 'USD');

        foreach ($this->items as $item) {
            $total = $total->add($item->subtotal);
        }

        $this->totalAmount = $total;
    }
}
```

And the `OrderItem` (an Entity internal to the Aggregate):

```php
<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Money;

class OrderItem
{
    public readonly string $productId;
    public readonly string $productName;
    public readonly Money $unitPrice;

    private(set) int $quantity;

    public function __construct(
        string $productId,
        string $productName,
        Money $unitPrice,
        int $quantity
    ) {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $this->productId = $productId;
        $this->productName = $productName;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
    }

    public function updateQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $this->quantity = $quantity;
    }

    public Money $subtotal {
        get => new Money(
            $this->unitPrice->amount * $this->quantity,
            $this->unitPrice->currency
        );
    }
}
```

**Important points:**

1. `Order` is the Aggregate Root
2. `OrderItem` can only be accessed through `Order`
3. All invariants (business rules) are protected by the Aggregate Root
4. The Aggregate guarantees transactional consistency

Notice that we don't have an `OrderItemRepository`. Order items are always manipulated through `Order`.

## DAO vs Repository

Now that we understand Domain Models and Aggregates, we can finally understand the difference between DAO and Repository. This is the part that most developers confuse.

### DAO (Data Access Object)

The DAO pattern emerged in the context of J2EE applications, documented in the book *Core J2EE Patterns* (Alur, Crupi, and Malks, 2001). It's a **data-oriented** pattern.

A DAO is an abstraction **close to the database**. It encapsulates access to a specific data source (usually a table) and provides CRUD operations.

**DAO Characteristics:**

- **Table-centric**: Usually one DAO per table
- **Database-oriented**: Methods reflect database operations (insert, update, delete, find)
- **Lower-level**: Closer to infrastructure
- **Data-focused**: Works with data, not domain behavior

**Note:** The following examples use Laravel with Eloquent ORM to demonstrate the concepts in a familiar context for PHP developers.

```php
<?php

namespace App\Infrastructure\DAO;

use App\Models\Order as OrderModel;
use Illuminate\Support\Collection;

class OrderDAO
{
    public function insert(array $data): int
    {
        $order = OrderModel::create($data);
        return $order->id;
    }

    public function update(int $id, array $data): bool
    {
        return OrderModel::where('id', $id)->update($data) > 0;
    }

    public function delete(int $id): bool
    {
        return OrderModel::destroy($id) > 0;
    }

    public function findById(int $id): ?array
    {
        $order = OrderModel::find($id);
        return $order?->toArray();
    }

    public function findByCustomerId(int $customerId): array
    {
        return OrderModel::where('customer_id', $customerId)
            ->get()
            ->toArray();
    }

    public function findByStatus(string $status): array
    {
        return OrderModel::where('status', $status)
            ->get()
            ->toArray();
    }
}
```

And a separate DAO for the items:

```php
<?php

namespace App\Infrastructure\DAO;

use App\Models\OrderItem as OrderItemModel;

class OrderItemDAO
{
    public function insert(array $data): int
    {
        $item = OrderItemModel::create($data);
        return $item->id;
    }

    public function deleteByOrderId(int $orderId): int
    {
        return OrderItemModel::where('order_id', $orderId)->delete();
    }

    public function findByOrderId(int $orderId): array
    {
        return OrderItemModel::where('order_id', $orderId)
            ->get()
            ->toArray();
    }

    public function update(int $id, array $data): bool
    {
        return OrderItemModel::where('id', $id)->update($data) > 0;
    }
}
```

**Notice:** DAOs work with arrays, not domain objects. They are agnostic to the domain model. Each DAO corresponds to a single table.

### Repository

The Repository pattern, on the other hand, emerged in the context of Domain-Driven Design, described by Eric Evans. It's a **domain-oriented** pattern.

> "A Repository represents all objects of a certain type as a conceptual set... It acts like an in-memory collection of domain objects."

**Repository Characteristics:**

- **Aggregate-centric**: One Repository per Aggregate Root
- **Domain-oriented**: Methods speak the domain language
- **Higher-level**: Abstraction over data access
- **Collection-like**: Simulates an in-memory collection
- **Encapsulates complexity**: Hides how data is mapped and persisted

### Repository in Domain-Driven Design Context

In Domain-Driven Design, there's a clear separation between layers:

- **Domain Layer**: Contains Entities, Value Objects, Aggregates, Domain Services, and **Repository Interfaces**
- **Infrastructure Layer**: Contains **Repository Implementations**, ORM configurations, external service integrations

The Repository **interface** belongs to the Domain layer because it's part of the domain's vocabulary. The domain knows it needs to "save an Order" or "find Orders by customer", but it doesn't care how that's done.

The Repository **implementation** belongs to the Infrastructure layer because it deals with technical details like Eloquent, database connections, and query building.

```text
app/
├── Domain/
│   ├── Aggregates/
│   │   └── Order.php
│   ├── Entities/
│   │   └── OrderItem.php
│   ├── ValueObjects/
│   │   ├── OrderId.php
│   │   ├── CustomerId.php
│   │   └── Money.php
│   └── Repositories/
│       └── OrderRepositoryInterface.php  <-- Interface (Domain Layer)
│
└── Infrastructure/
    └── Repositories/
        └── EloquentOrderRepository.php   <-- Implementation (Infrastructure Layer)
```

This separation allows you to:

1. Test domain logic without database dependencies
2. Swap implementations without changing domain code
3. Keep domain code clean and focused on business rules

### Repository Interface (Domain Layer)

```php
<?php

namespace App\Domain\Repositories;

use App\Domain\Aggregates\Order;
use App\Domain\ValueObjects\OrderId;
use App\Domain\ValueObjects\CustomerId;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function findById(OrderId $id): ?Order;

    public function findByCustomer(CustomerId $customerId): array;

    public function findPendingOrders(): array;

    public function delete(Order $order): void;
}
```

### Repository Implementation (Infrastructure Layer)

Now the implementation using Eloquent directly (without DAO):

```php
<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Aggregates\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Domain\ValueObjects\CustomerId;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\OrderId;
use App\Models\Order as OrderModel;
use App\Models\OrderItem as OrderItemModel;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Upsert the order
            OrderModel::updateOrCreate(
                ['id' => $order->getId()->getValue()],
                [
                    'customer_id' => $order->getCustomerId()->getValue(),
                    'status' => $order->getStatus(),
                    'total_amount' => $order->getTotalAmount()->getAmount(),
                    'currency' => $order->getTotalAmount()->getCurrency(),
                    'created_at' => $order->getCreatedAt(),
                ]
            );

            // Remove old items and insert new ones
            OrderItemModel::where('order_id', $order->getId()->getValue())->delete();

            foreach ($order->getItems() as $item) {
                OrderItemModel::create([
                    'order_id' => $order->getId()->getValue(),
                    'product_id' => $item->getProductId(),
                    'product_name' => $item->getProductName(),
                    'unit_price' => $item->getUnitPrice()->getAmount(),
                    'currency' => $item->getUnitPrice()->getCurrency(),
                    'quantity' => $item->getQuantity(),
                ]);
            }
        });
    }

    public function findById(OrderId $id): ?Order
    {
        $orderModel = OrderModel::with('items')->find($id->getValue());

        if (!$orderModel) {
            return null;
        }

        return $this->toDomainEntity($orderModel);
    }

    public function findByCustomer(CustomerId $customerId): array
    {
        $orders = OrderModel::with('items')
            ->where('customer_id', $customerId->getValue())
            ->get();

        return $orders->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findPendingOrders(): array
    {
        $orders = OrderModel::with('items')
            ->where('status', 'pending')
            ->get();

        return $orders->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function delete(Order $order): void
    {
        DB::transaction(function () use ($order) {
            OrderItemModel::where('order_id', $order->getId()->getValue())->delete();
            OrderModel::destroy($order->getId()->getValue());
        });
    }

    /**
     * Reconstitutes a domain Order aggregate from an Eloquent model.
     */
    private function toDomainEntity(OrderModel $model): Order
    {
        $order = new Order(
            new OrderId($model->id),
            new CustomerId($model->customer_id)
        );

        // Add items to the order
        foreach ($model->items as $itemModel) {
            $order->addItem(
                $itemModel->product_id,
                $itemModel->product_name,
                new Money($itemModel->unit_price, $itemModel->currency),
                $itemModel->quantity
            );
        }

        // Restore the status using reflection (since status might not be 'pending')
        if ($model->status !== 'pending') {
            $this->setPrivateProperty($order, 'status', $model->status);
        }

        // Restore the original created_at
        $this->setPrivateProperty($order, 'createdAt', new \DateTimeImmutable($model->created_at));

        return $order;
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object::class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }
}
```

**Key points:**

1. The Repository receives and returns **complete Aggregates** (Order with its Items)
2. The Repository handles multiple tables internally (orders + order_items)
3. The interface speaks the domain language (`findPendingOrders`, not `findByStatus`)
4. The mapping between tables and domain objects is encapsulated
5. Eloquent models are **infrastructure details**, not domain objects
6. The domain `Order` class has behavior; the Eloquent `Order` model is just for persistence

## Comparison Table: DAO vs Repository

| Aspect | DAO | Repository |
| --- | --- | --- |
| **Focus** | Data / Tables | Domain / Aggregates |
| **Granularity** | One per table | One per Aggregate Root |
| **Language** | Database terms (insert, update) | Domain terms (save, findPending) |
| **Return Type** | Arrays or DTOs | Domain objects (Aggregates) |
| **Abstraction** | Low-level | High-level |
| **Origin** | J2EE Patterns | Domain-Driven Design |
| **Use Case** | Simple CRUD | Complex domains |
| **Layer** | Infrastructure only | Interface in Domain, Implementation in Infrastructure |

## Repository Works Better with a Domain Model

The main point is: **Repositories make sense when you have a rich Domain Model**.

A Repository receives an Aggregate ID and returns a complete, hydrated Aggregate. It's the layer that translates between two worlds: the domain world (with Aggregates, Entities, and Value Objects) and the database world (with tables and relationships).

**One Aggregate can persist data across multiple tables.**

In our `Order` example:

- The domain has: 1 Aggregate (Order with OrderItems)
- The database has: 2 tables (orders and order_items)

This is a 1:2 relationship between Aggregate and tables.

We can have other proportions:

- 1:1 - A simple Aggregate in one table
- 1:3 - A complex Aggregate distributed across three tables
- 1:N - Aggregates with many internal entities

The Repository hides all this complexity from the rest of the application.

## Repos Without a Domain Model are DAOs?

If you don't have a rich Domain Model, if your "models" are just DTOs or anemic objects without behavior, then yes, **your Repository is basically a "glorified" DAO**.

And that's not necessarily wrong! Not every application needs DDD. Simple CRUD applications can work perfectly well with Active Record or DAOs.

The problem is when you use the "Repository" nomenclature but implement a DAO, losing all the benefits of the pattern.

## Are Laravel Eloquent Models DAOs?

This is a very common question in the Laravel ecosystem. The short answer: **Eloquent Models implement the Active Record pattern, not DAO nor Repository**.

The Active Record pattern, described by Martin Fowler in *Patterns of Enterprise Application Architecture*, combines data and persistence behavior in the same object. Each Model represents a row in the database and knows how to save and load itself.

```php
// Active Record - the model knows about persistence
$user = new User();
$user->name = 'John';
$user->save(); // The model itself knows how to save
```

**Active Record is different from both DAO and Repository:**

- **DAO**: Separate object that manages data access for a table
- **Repository**: Collection abstraction for Aggregate Roots
- **Active Record**: The data object itself knows how to persist

**Problems with using Active Record as Repository:**

Active Record couples your domain model to database infrastructure. This goes against the Repository principle of isolating the domain from persistence details.

```php
// This is NOT a real Repository - it's just a wrapper
class UserRepository
{
    public function find(int $id): User
    {
        return User::find($id); // Still using Active Record
    }

    public function save(User $user): void
    {
        $user->save(); // The model still knows about persistence
    }
}
```

This "Repository" doesn't provide real abstraction. The `User` is still an Eloquent Model that knows about the database.

**When is this acceptable?**

For most Laravel applications, using Eloquent directly is perfectly valid. Active Record is excellent for RAD (Rapid Application Development) and CRUD applications.

**When should you consider a "real" Repository?**

- When you have complex domain logic that needs to be tested in isolation
- When you want to swap persistence mechanisms (rare in practice)
- When you're strictly following DDD

## What I See Out There

Let me be honest about what I see in the real world:

### 1. Proxy Repos with Eloquent

```php
// This is very common and... questionable
class UserRepository
{
    public function all()
    {
        return User::all();
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }
}
```

This is not a Repository, it's a proxy that adds no value. The Eloquent Model already does all of this.

### 2. One Repo per Table

```php
// Too many repos, following DAO logic
class OrderRepository { }
class OrderItemRepository { }
class OrderStatusHistoryRepository { }
```

If `OrderItem` and `OrderStatusHistory` only exist in the context of an `Order`, you don't need separate repositories for them. The `OrderRepository` **should manage the complete Aggregate**.

### 3. Generic Interface for All

```php
interface RepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
```

This turns all Repositories into DAOs. Each Repository should have its own interface with domain-specific methods.

## Base Repository: Is It Worth It?

A common question: is it correct to have a Base Repository for CRUD operations and leave specific repositories only for extra queries?

```php
abstract class BaseRepository
{
    protected $model;

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function save($entity): void
    {
        $entity->save();
    }

    public function delete($entity): void
    {
        $entity->delete();
    }
}

class OrderRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Order();
    }

    // Domain-specific methods
    public function findPendingByCustomer(CustomerId $customerId): array
    {
        // ...
    }
}
```

**My opinion:** this works, but goes against the spirit of the Repository pattern.

**Problems:**

1. Assumes all Aggregates are persisted the same way
2. A complex Aggregate (like Order with Items) doesn't follow this pattern
3. Generic methods like `find($id)` don't speak the domain language
4. You lose the main advantage: swapping implementations

**When it can work:**

- Simpler applications where Aggregates map 1:1 to tables
- Teams starting with the pattern and needing something pragmatic
- Projects where complete abstraction isn't necessary

**The ideal alternative for DDD contexts:**

For that context, don't use inheritance and don't use a `BaseRepository`. Each repository should have its own implementation because each **Aggregate has different persistence needs**.

```php
<?php

// Order: complex aggregate (2 tables)
class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(OrderId $id): ?Order
    {
        $model = OrderModel::with('items')->find($id->value);

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Saves to 'orders' table
            // Saves to 'order_items' table
        });
    }

    private function toDomainEntity(OrderModel $model): Order { /* ... */ }
}

// Customer: simple aggregate (1 table)
class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(CustomerId $id): ?Customer
    {
        $model = CustomerModel::find($id->value);

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Customer $customer): void
    {
        // Saves to 'customers' table only
        CustomerModel::updateOrCreate([/* ... */]);
    }

    private function toDomainEntity(CustomerModel $model): Customer { /* ... */ }
}
```

**Alternative for simple CRUDs:**

If you need to share common CRUD logic for simple aggregates, use a **trait**:

```php
<?php

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    use CrudRepositoryTrait;  // Handles basic find, save, delete

    // Domain-specific methods - implemented manually
    public function findByEmail(Email $email): ?Customer { /* ... */ }
    public function findActiveCustomers(): array { /* ... */ }
}
```

The key difference from `BaseRepository`:

- **Interface stays domain-specific** — `CustomerRepositoryInterface` has methods like `findByEmail()`, not generic `find($id)`
- **Trait is just implementation detail** — helps reduce boilerplate for basic operations
- **Each repository still owns its mapping** — `toDomainEntity()` and `toDatabase()` are specific to each Aggregate

| Aggregate Type | Example | Use Trait? |
| --- | --- | --- |
| Simple (1:1 with table) | Customer, Product, Category | ✅ Can use |
| Complex (multiple tables) | Order + Items, Cart + Items | ❌ Don't use |

For complex aggregates like `Order` (which persists to `orders` + `order_items` tables), write the repository from scratch. The trait won't fit, and that's fine — each repository knows best how to persist its Aggregate.

## Conclusion

The Repository Pattern is powerful when used correctly, but frequently misunderstood.

**Key points:**

1. **Repository ≠ DAO**: Repository is domain-oriented, DAO is data-oriented
2. **One Repository per Aggregate Root**: Don't create repositories for internal entities
3. **Repository requires Domain Model**: Without a rich Domain Model, you just have a disguised DAO
4. **Eloquent is Active Record**: It's not DAO nor Repository
5. **Interface should speak the domain language**: `findPendingOrders()`, not `findByStatus('pending')`
6. **Interface in Domain, Implementation in Infrastructure**: This separation is key in DDD

Before implementing the Repository Pattern, ask yourself: "Do I really need this?"

If your application is simple CRUD, Active Record (Eloquent) solves it perfectly. If you have complex domain logic, business invariants, and need real testability, then yes, Repository might be the right choice.

## References

- **Evans, Eric.** *Domain-Driven Design: Tackling Complexity in the Heart of Software*. Addison-Wesley, 2003. (Blue Book)
- **Vernon, Vaughn.** *Implementing Domain-Driven Design*. Addison-Wesley, 2013. (Red Book)
- **Khononov, Vlad.** *Learning Domain-Driven Design: Aligning Software Architecture and Business Strategy*. O'Reilly Media, 2021.
- **Alur, Deepak; Crupi, John; Malks, Dan.** *Core J2EE Patterns: Best Practices and Design Strategies*. Prentice Hall, 2001.
- **Fowler, Martin.** *Patterns of Enterprise Application Architecture*. Addison-Wesley, 2002.
- **Evans, Eric.** *Domain-Driven Design Reference: Definitions and Pattern Summaries*. Domain Language, 2015.
