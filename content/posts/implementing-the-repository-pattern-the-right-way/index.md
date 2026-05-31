---
title: 'Implementando o Repository Pattern — do jeito certo'
excerpt: 'Repositórios não são "só proxies para ORMs". Um mergulho em Domain Models, Aggregates, DAO vs Repository, e como implementar o padrão Repository do jeito certo — e quando usá-lo.'
author: Lucas Sahdo
image: capa.webp
tags:
  - php
  - ddd
  - repository
  - laravel
  - arquitetura
published_at: '2026-01-30'
draft: false
newsletter_sent_at: ~
---

Você já encontrou alguém dizendo: "Repositórios são inúteis! São só camadas de proxy para ORMs"? Se você já ouviu isso, saiba que essa pessoa provavelmente nunca entendeu de verdade para que serve esse padrão de projeto.

Neste artigo, vamos explicar de ponta a ponta o propósito do padrão Repository, as formas erradas e certas de implementá-lo e, o mais importante: quando usá-lo.

Mas antes de mergulharmos nos repositórios, precisamos entender o que é um Domain Model (Modelo de Domínio), senão a explicação do padrão não vai fazer muito sentido. Então, você já ouviu falar de Domain Models?

## Domain Models

> "Um modelo é uma representação simplificada de uma coisa ou fenômeno que intencionalmente enfatiza certos aspectos enquanto ignora outros."

O Domain Model é o coração do software no Domain-Driven Design. Ele representa não apenas dados, mas também comportamentos e regras de negócio do domínio que estamos modelando. Como Eric Evans descreve:

> "O coração do software é a sua capacidade de resolver problemas relacionados ao domínio para o usuário. Todas as outras funcionalidades, por mais vitais que sejam, dão suporte a esse propósito básico."

Um Domain Model é composto por vários blocos de construção. Vamos entender os principais:

### Entities

Uma **Entity** (Entidade) é um objeto que tem uma identidade única que persiste ao longo do tempo, mesmo quando seus atributos mudam. A identidade é o que define uma Entity, não os seus valores.

Pense assim: você pode mudar de endereço, de telefone, até de nome, mas você continua sendo você. Sua identidade (seu CPF, por exemplo) permanece a mesma.

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

Note que o `Customer` pode mudar o endereço e o e-mail, mas o `CustomerId` permanece o mesmo. Dois clientes com o mesmo nome e e-mail, mas IDs diferentes, são clientes **diferentes**.

### Value Objects

Diferente das Entities, os **Value Objects** (Objetos de Valor) são definidos por seus atributos, não por uma identidade. Eles são imutáveis e comparados por valor.

Pense no dinheiro: uma nota de R$100 é igual a qualquer outra nota de R$100. Não importa qual nota específica você tem, o que importa é o valor.

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

Outro exemplo clássico é o `Address`:

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

Value Objects são imutáveis. Se você precisa "mudar" um Value Object, você cria um novo.

### Aggregates

Aqui chegamos ao conceito mais importante para entender Repositories corretamente.

> "Um Aggregate é um cluster de objetos de domínio que tratamos como uma unidade para fins de alterações de dados."

Um Aggregate define uma fronteira de consistência. Ele agrupa Entities e Value Objects que precisam ser modificados juntos para manter as regras de negócio consistentes.

Todo Aggregate tem uma **Aggregate Root** (Raiz do Agregado): a Entity principal pela qual todo o acesso externo deve passar. Objetos externos só podem referenciar a Aggregate Root, nunca as entidades internas diretamente.

Vaughn Vernon enfatiza:

> "Prefira referências a Aggregates externos apenas por sua identidade globalmente única, em vez de manter uma referência direta ao objeto."

### Exemplo prático: Order

Vamos usar um exemplo clássico de e-commerce: um pedido com seus itens.

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

E o `OrderItem` (uma Entity interna ao Aggregate):

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

**Pontos importantes:**

1. `Order` é a Aggregate Root
2. `OrderItem` só pode ser acessado através de `Order`
3. Todas as invariantes (regras de negócio) são protegidas pela Aggregate Root
4. O Aggregate garante a consistência transacional

Note que não temos um `OrderItemRepository`. Os itens do pedido são sempre manipulados através de `Order`.

## DAO vs Repository

Agora que entendemos Domain Models e Aggregates, podemos finalmente entender a diferença entre DAO e Repository. Esta é a parte que a maioria dos desenvolvedores confunde.

### DAO (Data Access Object)

O padrão DAO surgiu no contexto de aplicações J2EE, documentado no livro *Core J2EE Patterns* (Alur, Crupi e Malks, 2001). É um padrão **orientado a dados**.

Um DAO é uma abstração **próxima do banco de dados**. Ele encapsula o acesso a uma fonte de dados específica (geralmente uma tabela) e fornece operações CRUD.

**Características do DAO:**

- **Centrado em tabela**: Geralmente um DAO por tabela
- **Orientado ao banco**: Os métodos refletem operações de banco (insert, update, delete, find)
- **Mais baixo nível**: Mais perto da infraestrutura
- **Focado em dados**: Trabalha com dados, não com comportamento de domínio

**Nota:** Os exemplos a seguir usam Laravel com o ORM Eloquent para demonstrar os conceitos em um contexto familiar para desenvolvedores PHP.

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

E um DAO separado para os itens:

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

**Note:** DAOs trabalham com arrays, não com objetos de domínio. Eles são agnósticos ao modelo de domínio. Cada DAO corresponde a uma única tabela.

### Repository

O padrão Repository, por outro lado, surgiu no contexto do Domain-Driven Design, descrito por Eric Evans. É um padrão **orientado ao domínio**.

> "Um Repository representa todos os objetos de um certo tipo como um conjunto conceitual... Ele age como uma coleção em memória de objetos de domínio."

**Características do Repository:**

- **Centrado em Aggregate**: Um Repository por Aggregate Root
- **Orientado ao domínio**: Os métodos falam a linguagem do domínio
- **Mais alto nível**: Abstração sobre o acesso a dados
- **Como uma coleção**: Simula uma coleção em memória
- **Encapsula complexidade**: Esconde como os dados são mapeados e persistidos

### Repository no contexto do Domain-Driven Design

No Domain-Driven Design, há uma separação clara entre camadas:

- **Camada de Domínio**: Contém Entities, Value Objects, Aggregates, Domain Services e as **Interfaces de Repository**
- **Camada de Infraestrutura**: Contém as **Implementações de Repository**, configurações de ORM, integrações com serviços externos

A **interface** do Repository pertence à camada de Domínio porque faz parte do vocabulário do domínio. O domínio sabe que precisa "salvar um Order" ou "encontrar Orders por cliente", mas não se importa com como isso é feito.

A **implementação** do Repository pertence à camada de Infraestrutura porque lida com detalhes técnicos como Eloquent, conexões de banco e construção de queries.

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
│       └── OrderRepositoryInterface.php  <-- Interface (Camada de Domínio)
│
└── Infrastructure/
    └── Repositories/
        └── EloquentOrderRepository.php   <-- Implementação (Camada de Infraestrutura)
```

Essa separação permite que você:

1. Teste a lógica de domínio sem dependências de banco de dados
2. Troque implementações sem mudar o código de domínio
3. Mantenha o código de domínio limpo e focado nas regras de negócio

### Interface do Repository (Camada de Domínio)

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

### Implementação do Repository (Camada de Infraestrutura)

Agora a implementação usando Eloquent diretamente (sem DAO):

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

**Pontos-chave:**

1. O Repository recebe e retorna **Aggregates completos** (Order com seus Items)
2. O Repository lida com múltiplas tabelas internamente (orders + order_items)
3. A interface fala a linguagem do domínio (`findPendingOrders`, não `findByStatus`)
4. O mapeamento entre tabelas e objetos de domínio é encapsulado
5. Os models do Eloquent são **detalhes de infraestrutura**, não objetos de domínio
6. A classe de domínio `Order` tem comportamento; o model `Order` do Eloquent é só para persistência

## Tabela comparativa: DAO vs Repository

| Aspecto | DAO | Repository |
| --- | --- | --- |
| **Foco** | Dados / Tabelas | Domínio / Aggregates |
| **Granularidade** | Um por tabela | Um por Aggregate Root |
| **Linguagem** | Termos de banco (insert, update) | Termos de domínio (save, findPending) |
| **Tipo de retorno** | Arrays ou DTOs | Objetos de domínio (Aggregates) |
| **Abstração** | Baixo nível | Alto nível |
| **Origem** | J2EE Patterns | Domain-Driven Design |
| **Caso de uso** | CRUD simples | Domínios complexos |
| **Camada** | Só Infraestrutura | Interface no Domínio, Implementação na Infraestrutura |

## Repository funciona melhor com um Domain Model

O ponto principal é: **Repositories fazem sentido quando você tem um Domain Model rico**.

Um Repository recebe o ID de um Aggregate e retorna um Aggregate completo e hidratado. É a camada que traduz entre dois mundos: o mundo do domínio (com Aggregates, Entities e Value Objects) e o mundo do banco de dados (com tabelas e relacionamentos).

**Um Aggregate pode persistir dados em múltiplas tabelas.**

No nosso exemplo de `Order`:

- O domínio tem: 1 Aggregate (Order com OrderItems)
- O banco tem: 2 tabelas (orders e order_items)

Isso é uma relação 1:2 entre Aggregate e tabelas.

Podemos ter outras proporções:

- 1:1 - Um Aggregate simples em uma tabela
- 1:3 - Um Aggregate complexo distribuído em três tabelas
- 1:N - Aggregates com muitas entidades internas

O Repository esconde toda essa complexidade do resto da aplicação.

## Repos sem Domain Model são DAOs?

Se você não tem um Domain Model rico, se os seus "models" são só DTOs ou objetos anêmicos sem comportamento, então sim, **o seu Repository é basicamente um DAO "glorificado"**.

E isso não está necessariamente errado! Nem toda aplicação precisa de DDD. Aplicações CRUD simples podem funcionar perfeitamente com Active Record ou DAOs.

O problema é quando você usa a nomenclatura "Repository" mas implementa um DAO, perdendo todos os benefícios do padrão.

## Os models Eloquent do Laravel são DAOs?

Esta é uma pergunta muito comum no ecossistema Laravel. A resposta curta: **os Models do Eloquent implementam o padrão Active Record, não DAO nem Repository**.

O padrão Active Record, descrito por Martin Fowler em *Patterns of Enterprise Application Architecture*, combina dados e comportamento de persistência no mesmo objeto. Cada Model representa uma linha no banco e sabe como salvar e carregar a si mesmo.

```php
// Active Record - the model knows about persistence
$user = new User();
$user->name = 'John';
$user->save(); // The model itself knows how to save
```

**O Active Record é diferente tanto de DAO quanto de Repository:**

- **DAO**: Objeto separado que gerencia o acesso a dados de uma tabela
- **Repository**: Abstração de coleção para Aggregate Roots
- **Active Record**: O próprio objeto de dados sabe como se persistir

**Problemas de usar Active Record como Repository:**

O Active Record acopla o seu modelo de domínio à infraestrutura de banco. Isso vai contra o princípio do Repository de isolar o domínio dos detalhes de persistência.

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

Esse "Repository" não fornece uma abstração real. O `User` ainda é um Model do Eloquent que conhece o banco de dados.

**Quando isso é aceitável?**

Para a maioria das aplicações Laravel, usar o Eloquent diretamente é perfeitamente válido. Active Record é excelente para RAD (Desenvolvimento Rápido de Aplicações) e aplicações CRUD.

**Quando você deveria considerar um Repository "de verdade"?**

- Quando você tem lógica de domínio complexa que precisa ser testada isoladamente
- Quando você quer trocar o mecanismo de persistência (raro na prática)
- Quando você está seguindo DDD estritamente

## O que eu vejo por aí

Deixa eu ser honesto sobre o que eu vejo no mundo real:

### 1. Repos-proxy com Eloquent

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

Isso não é um Repository, é um proxy que não agrega nenhum valor. O Model do Eloquent já faz tudo isso.

### 2. Um Repo por tabela

```php
// Too many repos, following DAO logic
class OrderRepository { }
class OrderItemRepository { }
class OrderStatusHistoryRepository { }
```

Se `OrderItem` e `OrderStatusHistory` só existem no contexto de um `Order`, você não precisa de repositórios separados para eles. O `OrderRepository` **deve gerenciar o Aggregate completo**.

### 3. Interface genérica para tudo

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

Isso transforma todos os Repositories em DAOs. Cada Repository deveria ter a sua própria interface com métodos específicos do domínio.

## Base Repository: vale a pena?

Uma pergunta comum: é correto ter um Base Repository para operações CRUD e deixar os repositórios específicos apenas para queries extras?

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

**Minha opinião:** isso funciona, mas vai contra o espírito do padrão Repository.

**Problemas:**

1. Assume que todos os Aggregates são persistidos da mesma forma
2. Um Aggregate complexo (como Order com Items) não segue esse padrão
3. Métodos genéricos como `find($id)` não falam a linguagem do domínio
4. Você perde a principal vantagem: trocar implementações

**Quando pode funcionar:**

- Aplicações mais simples onde os Aggregates mapeiam 1:1 para tabelas
- Times começando com o padrão e que precisam de algo pragmático
- Projetos onde a abstração completa não é necessária

**A alternativa ideal para contextos de DDD:**

Para esse contexto, não use herança e não use um `BaseRepository`. Cada repositório deveria ter a sua própria implementação porque cada **Aggregate tem necessidades de persistência diferentes**.

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

**Alternativa para CRUDs simples:**

Se você precisa compartilhar lógica de CRUD comum para aggregates simples, use uma **trait**:

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

A diferença-chave em relação ao `BaseRepository`:

- **A interface continua específica do domínio** — `CustomerRepositoryInterface` tem métodos como `findByEmail()`, não um `find($id)` genérico
- **A trait é só um detalhe de implementação** — ajuda a reduzir boilerplate para operações básicas
- **Cada repositório ainda é dono do seu mapeamento** — `toDomainEntity()` e `toDatabase()` são específicos de cada Aggregate

| Tipo de Aggregate | Exemplo | Usar trait? |
| --- | --- | --- |
| Simples (1:1 com tabela) | Customer, Product, Category | ✅ Pode usar |
| Complexo (múltiplas tabelas) | Order + Items, Cart + Items | ❌ Não use |

Para aggregates complexos como `Order` (que persiste nas tabelas `orders` + `order_items`), escreva o repositório do zero. A trait não vai encaixar, e tudo bem — cada repositório sabe melhor como persistir o seu Aggregate.

## Conclusão

O Repository Pattern é poderoso quando usado corretamente, mas frequentemente mal compreendido.

**Pontos-chave:**

1. **Repository ≠ DAO**: Repository é orientado ao domínio, DAO é orientado a dados
2. **Um Repository por Aggregate Root**: Não crie repositórios para entidades internas
3. **Repository requer Domain Model**: Sem um Domain Model rico, você só tem um DAO disfarçado
4. **Eloquent é Active Record**: Não é DAO nem Repository
5. **A interface deve falar a linguagem do domínio**: `findPendingOrders()`, não `findByStatus('pending')`
6. **Interface no Domínio, Implementação na Infraestrutura**: Essa separação é fundamental no DDD

Antes de implementar o Repository Pattern, pergunte a si mesmo: "Eu realmente preciso disso?"

Se a sua aplicação é um CRUD simples, o Active Record (Eloquent) resolve perfeitamente. Se você tem lógica de domínio complexa, invariantes de negócio e precisa de testabilidade real, então sim, o Repository pode ser a escolha certa.

## Referências

- **Evans, Eric.** *Domain-Driven Design: Tackling Complexity in the Heart of Software*. Addison-Wesley, 2003. (Blue Book)
- **Vernon, Vaughn.** *Implementing Domain-Driven Design*. Addison-Wesley, 2013. (Red Book)
- **Khononov, Vlad.** *Learning Domain-Driven Design: Aligning Software Architecture and Business Strategy*. O'Reilly Media, 2021.
- **Alur, Deepak; Crupi, John; Malks, Dan.** *Core J2EE Patterns: Best Practices and Design Strategies*. Prentice Hall, 2001.
- **Fowler, Martin.** *Patterns of Enterprise Application Architecture*. Addison-Wesley, 2002.
- **Evans, Eric.** *Domain-Driven Design Reference: Definitions and Pattern Summaries*. Domain Language, 2015.
