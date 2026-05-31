---
title: 'Além do CRUD: construindo uma aplicação orientada a eventos com CQRS e filas de mensagens'
excerpt: 'Por que ler dados precisa funcionar do mesmo jeito que escrevê-los? Um olhar prático sobre CQRS e arquitetura orientada a eventos em PHP com Slim, RabbitMQ, PostgreSQL e MongoDB.'
author: Lucas Sahdo
image: capa.jpeg
tags:
  - php
  - cqrs
  - orientado-a-eventos
  - arquitetura
  - slim
published_at: '2026-01-05'
draft: false
newsletter_sent_at: ~
---

Como engenheiros de software, todos nós já passamos por isso: construir mais uma aplicação CRUD com um único banco de dados, onde operações de leitura e escrita disputam recursos, consultas complexas ficam lentas conforme os dados crescem, e escalar vira um pesadelo. Por anos, essa abordagem monolítica funcionou bem o suficiente para a maioria das aplicações. Mas o que acontece quando o seu sistema precisa lidar com milhares de leituras concorrentes enquanto processa escritas críticas? E se você precisar de estruturas de dados diferentes para exibir informações e para armazená-las?

É aí que entram o **CQRS** (Command Query Responsibility Segregation) e a **Arquitetura Orientada a Eventos** – padrões que desafiam a forma tradicional de construir aplicações respondendo a uma pergunta simples, mas importante: ***Por que ler dados precisa funcionar do mesmo jeito que escrevê-los?***

Neste artigo, vou te guiar na construção de um exemplo simples de um Sistema de Gestão de Pedidos que implementa CQRS com PHP 8.4, usando Slim Framework, RabbitMQ, PostgreSQL e MongoDB. Mas, mais importante, vou explicar *por que* você pode querer usar esses padrões, *quando* eles fazem sentido e *como* implementá-los sem complicar demais a sua arquitetura.

## O que vamos construir

Vamos criar um sistema onde:

- **Operações de escrita** (criar/atualizar pedidos) vão para o PostgreSQL
- **Operações de leitura** (consultar pedidos) vêm do MongoDB
- **Eventos** conectam os dois lados através do RabbitMQ
- Tudo é containerizado com Docker para facilitar a configuração

Ao final, você vai entender não só como o CQRS funciona na teoria, mas como implementá-lo em PHP.

## Por que isso importa

Aplicações CRUD tradicionais enfrentam desafios reais:

- Consultas de leitura podem bloquear operações de escrita
- Joins complexos ficam lentos conforme os dados crescem
- Escalar leitura e escrita de forma independente é difícil
- Uma única estrutura de banco precisa servir a todos os propósitos

O CQRS resolve esses problemas reconhecendo uma verdade fundamental: **a forma como escrevemos os dados raramente é a forma ideal de lê-los.**

## O que é CQRS, de verdade?

CQRS significa **Command Query Responsibility Segregation** (Segregação de Responsabilidade entre Comando e Consulta). A ideia é simples: separar o código que altera os dados do código que lê os dados.

Em aplicações típicas, tudo bate no mesmo banco. Um único banco lida com tudo, com uma estrutura que tenta ser boa tanto para leitura quanto para escrita. Normalmente acaba mediano nas duas.

O CQRS **divide essa responsabilidade**. Operações de escrita usam um modelo e operações de leitura usam outro. Eles podem compartilhar o mesmo banco ou usar bancos diferentes. No nosso projeto, vamos além usando PostgreSQL para escrita e MongoDB para leitura, com o RabbitMQ mantendo-os em sincronia. Quando algo muda no lado da escrita, um evento é enviado pela fila de mensagens. Um consumer o captura e atualiza o lado da leitura.

O ponto-chave é que o CQRS trata de separar os modelos e as responsabilidades, não necessariamente de ter dois bancos. Você poderia ter CQRS com um único banco usando tabelas diferentes ou até consultas diferentes nas mesmas tabelas. A abordagem de dois bancos com filas de mensagens é apenas uma das formas de implementá-lo.

Isso faz sentido quando suas leituras superam em muito as escritas. Um e-commerce pode ter dez mil pessoas navegando, mas só cinquenta fazendo compras. Também ajuda quando leitura e escrita precisam de coisas diferentes. Escrever exige integridade de dados e transações. Ler exige velocidade e dados desnormalizados.

**Evite CQRS em apps CRUD simples, painéis administrativos ou ferramentas internas.** Se o seu banco não está lento e suas leituras e escritas são mais ou menos equivalentes, **você está apenas adicionando complexidade sem motivo**.

A arquitetura orientada a eventos combina bem com CQRS, mas não é obrigatória. Você poderia implementar CQRS atualizando ambos os modelos de forma síncrona e direta. No nosso projeto, usamos eventos porque eles trazem flexibilidade. Quando um usuário cria um pedido, você trata a escrita e cria um evento. Esse evento vai para o RabbitMQ. Vários listeners podem reagir: um atualiza o MongoDB, outro envia um e-mail, outro registra analytics. Seu código de escrita continua simples. Ele apenas diz "um pedido foi criado" e segue em frente. Todo o resto acontece em segundo plano.

O trade-off é a consistência eventual. Seu banco de leitura não atualiza instantaneamente. Há um pequeno atraso enquanto o evento percorre a fila de mensagens. Para a maioria das aplicações, um atraso de alguns milissegundos é aceitável.

Como Greg Young aponta em sua documentação sobre CQRS, "os lados de comando e de consulta têm necessidades muito diferentes". O lado de comando processa transações com dados consistentes e precisa de garantias fortes. Já o lado de consulta pode ser eventualmente consistente na maioria dos sistemas. Essa diferença é, na verdade, uma vantagem: é muito mais fácil processar transações com dados consistentes do que lidar com todos os casos extremos que a consistência eventual traz para o lado da escrita.

Mas se você está construindo algo como um software bancário, onde os dados precisam ser imediatamente consistentes, é preciso pensar com cuidado nesse trade-off.

## Uma palavra de cautela

Martin Fowler, uma das vozes mais respeitadas em arquitetura de software, faz um alerta importante sobre o CQRS. Ele observa que "embora o CQRS possa beneficiar alguns domínios complexos, essa adequação é claramente um caso minoritário". Normalmente há sobreposição suficiente entre os lados de comando e de consulta para que compartilhar um modelo seja mais fácil. Usar CQRS em um domínio que não se encaixa nele vai adicionar complexidade, **reduzindo a produtividade e aumentando o risco**.

Vale levar isso a sério. **CQRS não é uma arquitetura padrão.** É uma ferramenta especializada para problemas específicos. A maioria das aplicações é mais bem atendida por uma arquitetura tradicional, onde leitura e escrita compartilham o mesmo modelo. A complexidade que você adiciona com o CQRS precisa se pagar com benefícios claros: melhor escalabilidade, separação de responsabilidades mais nítida ou a capacidade de otimizar leitura e escrita de formas diferentes.

Antes de implementar CQRS, pergunte-se: eu realmente tenho esses problemas? Se a sua aplicação funciona bem com um único banco e consultas tradicionais, fique com isso. Simples é melhor do que esperto.

## O projeto: Sistema de Gestão de Pedidos

Para entender o CQRS na prática, construímos um sistema de gestão de pedidos. A aplicação é simples o suficiente para se entender rápido, mas completa o bastante para demonstrar todas as peças em movimento.

O sistema lida com operações básicas de pedidos. Você pode criar pedidos, atualizar o status deles e consultá-los. Nada de mais. Mas, por baixo dos panos, as escritas vão para o PostgreSQL enquanto as leituras vêm do MongoDB, com o RabbitMQ mantendo tudo sincronizado.

### Visão geral da arquitetura

Veja como as peças se encaixam:

```text
+-------------+
|   client    |
+------+------+
       |
       | post /orders
       v
+-----------------+
|   slim api      |
|  (commands)     |
+--------+--------+
         |
         v
+-----------------+
|  postgresql     |
|  (write db)     |
+--------+--------+
         |
         | event: ordercreated
         v
+-----------------+
|   rabbitmq      |
|   (queue)       |
+--------+--------+
         |
         v
+-----------------+
|   consumer      |
|   (listener)    |
+--------+--------+
         |
         v
+-----------------+
|   mongodb       |
|   (read db)     |
+--------+--------+
         |
         | get /orders
         ^
+-----------------+
|   slim api      |
|   (queries)     |
+-----------------+
```

Quando um cliente cria um pedido, a requisição chega à nossa Slim API. A API executa um comando que salva o pedido no PostgreSQL. Nesse momento, o pedido existe no banco de escrita, mas em nenhum outro lugar.

Em seguida, o command handler dispara um evento. Esse evento é publicado no RabbitMQ, onde fica numa fila esperando para ser processado. A API responde ao cliente imediatamente, sem esperar mais nada acontecer.

Enquanto isso, um processo consumer separado roda continuamente, escutando a fila do RabbitMQ. Quando ele vê o evento de pedido criado, puxa os dados relevantes e os grava no MongoDB numa estrutura otimizada para leitura.

Agora, quando um cliente consulta pedidos, a API lê diretamente do MongoDB. Sem joins, sem consultas complexas. Os dados já estão moldados exatamente como precisamos.

### A estrutura de diretórios

```text
event-driven-cqrs-php/
│
├── api/
│   ├── src/
│   │   ├── Application/         ← Camada HTTP
│   │   │   ├── Actions/         (Handlers de requisição)
│   │   │   ├── Command/         (Operações de escrita)
│   │   │   └── Query/           (Operações de leitura)
│   │   │
│   │   ├── Domain/              ← Lógica de negócio
│   │   │   └── Order/
│   │   │       ├── Event/       (Eventos de domínio)
│   │   │       └── Order.php    (Entidade)
│   │   │
│   │   └── Infrastructure/      ← Detalhes técnicos
│   │       ├── Consumer/        (Listener do RabbitMQ)
│   │       ├── Persistence/     (PostgreSQL)
│   │       └── Query/           (MongoDB)
│   │
│   └── bin/
│       └── consumer.php         ← Worker em segundo plano
│
└── docker/
    ├── php/
    └── postgres/
```

O projeto segue uma arquitetura limpa com três camadas principais.

A camada **Application** contém tudo relacionado ao tratamento de requisições. As Actions são os handlers HTTP que recebem requisições e retornam respostas. Os Commands representam operações de escrita com seus handlers. As Queries representam operações de leitura com seus handlers.

A camada **Domain** guarda a lógica de negócio. É aqui que vive a entidade Order, junto com eventos de domínio como OrderCreated e OrderStatusChanged. O domínio não sabe nada sobre bancos de dados ou HTTP. Ele apenas modela o que é um pedido e o que pode acontecer com ele.

A camada **Infrastructure** cuida de todos os detalhes técnicos. Conexões com bancos, implementações de repositórios, listeners de eventos e o consumer do RabbitMQ vivem todos aqui. Essa camada é a ponte entre o seu domínio e o mundo externo.

### O fluxo em detalhe

Vamos acompanhar passo a passo o que acontece quando você cria um pedido:

```text
1. Requisição do cliente
   POST /orders
   {
     "customer_name": "John Doe",
     "items": [...]
   }

2. Action Handler
   ↓
   CreateOrderCommand

3. Command Handler
   ↓
   Salva no PostgreSQL
   ↓
   Dispara o evento OrderCreated

4. Event Listener
   ↓
   Publica no RabbitMQ
   ↓
   [API responde ao cliente]

5. Consumer (background)
   ↓
   Lê do RabbitMQ
   ↓
   Escreve no MongoDB

6. Hora da consulta
   GET /orders
   ↓
   Lê do MongoDB
   ↓
   Retorna a resposta
```

A requisição chega como um POST para `/orders` com dados em JSON. Um Action handler a recebe e cria um CreateOrderCommand com os dados da requisição. Esse comando é passado para um CreateOrderCommandHandler.

O command handler faz duas coisas. Primeiro, salva o pedido no PostgreSQL usando um repositório. Segundo, dispara um evento OrderCreated usando o dispatcher do League Event.

Um event listener captura imediatamente esse evento e publica uma mensagem no RabbitMQ. A mensagem contém o ID do pedido e os dados relevantes. Nesse ponto, a requisição da API está concluída e devolve uma resposta ao cliente.

O processo consumer, que roda separadamente no seu próprio container, captura a mensagem do RabbitMQ. Ele lê os dados do pedido e os grava no MongoDB em formato desnormalizado. Agora o modelo de leitura está atualizado.

Quando alguém consulta pedidos, ele acessa GET `/orders`. Um Query handler recebe a requisição e busca os dados diretamente do MongoDB. Rápido, simples, sem consultas complexas.

### Estruturas dos bancos

Veja como o mesmo pedido aparece em cada banco:

**PostgreSQL (Modelo de Escrita - Normalizado):**

```text
tabela orders:
┌─────────────────────────────────────┬─────────────┬─────────┬────────────┐
│ id                                  │ customer_id │ total   │ status     │
├─────────────────────────────────────┼─────────────┼─────────┼────────────┤
│ 550e8400-e29b-41d4-a716-446655440000│ 123         │ 1049.99 │ pending    │
└─────────────────────────────────────┴─────────────┴─────────┴────────────┘

tabela order_items:
┌──────────┬────────────────────────────────────┬──────────┬──────────┐
│ order_id │ product_id                         │ quantity │ price    │
├──────────┼────────────────────────────────────┼──────────┼──────────┤
│ 550e...  │ prod_001                           │ 1        │ 999.99   │
│ 550e...  │ prod_002                           │ 2        │ 25.00    │
└──────────┴────────────────────────────────────┴──────────┴──────────┘
```

**MongoDB (Modelo de Leitura - Desnormalizado):**

```json
{
  "_id": "550e8400-e29b-41d4-a716-446655440000",
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "items": [
    {"product": "Laptop", "quantity": 1, "price": 999.99},
    {"product": "Mouse", "quantity": 2, "price": 25.00}
  ],
  "total_amount": 1049.99,
  "status": "pending",
  "created_at": "2024-01-15T10:30:00Z"
}
```

O PostgreSQL mantém tudo normalizado para garantir a integridade dos dados. O MongoDB já tem tudo pré-juntado e pronto para exibir.

### Por que essa estrutura funciona

A separação de responsabilidades é clara. Comandos nunca tocam no MongoDB. Consultas nunca tocam no PostgreSQL. Os eventos fluem em uma única direção, da escrita para a leitura.

Você pode escalar cada peça de forma independente. Precisa lidar com mais leituras? Adicione réplicas do MongoDB. Precisa processar eventos mais rápido? Rode múltiplas instâncias do consumer. O banco de escrita pode permanecer pequeno e focado.

Testar também fica mais fácil. Você pode testar command handlers sem se preocupar com o modelo de leitura. Pode testar query handlers sem configurar o processamento de eventos. Cada peça tem um trabalho e o faz bem.

Vamos mergulhar e ver como isso funciona na prática.

## Olhando o código

Agora vamos ver como isso realmente funciona na prática. Vou te guiar pelo fluxo completo, da criação de um pedido até consultá-lo de volta.

O código-fonte completo está disponível aqui: <https://github.com/sahdoio/event-driven-cqrs-php>

Clone o projeto na sua máquina e então suba os containers do docker.

### A configuração do Docker

![Configuração do Docker](docker-setup.png)

Para iniciar a aplicação, digite na pasta raiz do projeto:

```bash
make go
```

### O Command: o que queremos fazer

Quando alguém cria um pedido, começamos com um objeto de comando. É só um contêiner de dados simples:

```php
class CreateOrderCommand
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $items,
        public readonly float $totalAmount
    ) {}
}
```

Nada de mais. Apenas os dados necessários para criar um pedido.

### O Command Handler: fazendo o trabalho

O command handler recebe esse comando e faz duas coisas:

```php
class CreateOrderHandler
{
    public function __construct(
        private readonly PostgresOrderRepository $orderRepository,
        private readonly EventDispatcher $eventDispatcher
    ) {}

    public function handle(CreateOrderCommand $command): string
    {
        // 1. Cria e salva o pedido no PostgreSQL
        $orderId = Uuid::uuid7();
        $order = new Order(
            $orderId,
            $command->customerName,
            $command->customerEmail,
            $command->items,
            $command->totalAmount,
            OrderStatus::PENDING
        );

        $this->orderRepository->save($order);

        // 2. Dispara um evento
        $event = new OrderCreated(
            $orderId->toString(),
            $order->getCustomerName(),
            $order->getCustomerEmail(),
            $order->getItems(),
            $order->getTotalAmount(),
            $order->getStatus()->value,
            $order->getCreatedAt()->format('Y-m-d H:i:s')
        );

        $this->eventDispatcher->dispatch($event);

        return $orderId->toString();
    }
}
```

Primeiro ele cria a entidade Order e a salva no PostgreSQL. Depois cria um evento e o dispara. O handler não sabe o que acontece com esse evento. O trabalho dele está feito.

### O Repository: conversando com o PostgreSQL

O repositório cuida dos detalhes do banco:

```php
public function save(Order $order): void
{
    $sql = "INSERT INTO orders (id, customer_name, customer_email, items, total_amount, status, created_at, updated_at)
            VALUES (:id, :customer_name, :customer_email, :items, :total_amount, :status, :created_at, :updated_at)
            ON CONFLICT (id) DO UPDATE SET ...";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id' => $order->getId()->toString(),
        'customer_name' => $order->getCustomerName(),
        'customer_email' => $order->getCustomerEmail(),
        'items' => json_encode($order->getItems()),
        'total_amount' => $order->getTotalAmount(),
        'status' => $order->getStatus()->value,
        'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
        'updated_at' => $order->getUpdatedAt()?->format('Y-m-d H:i:s'),
    ]);
}
```

Inserção padrão no banco. O pedido agora está no PostgreSQL.

![Salvando no PostgreSQL](postgres-save.png)

![Disparo do evento](event-dispatch.png)

### Publicando no RabbitMQ

Quando o event dispatcher dispara o evento OrderCreated, o RabbitMQEventPublisher o captura:

```php
class RabbitMQEventPublisher implements Listener
{
    public function __invoke(object $event): void
    {
        $eventName = method_exists($event, 'eventName') ? $event->eventName() : get_class($event);
        $payload = method_exists($event, 'toArray') ? $event->toArray() : ['event' => $eventName];

        $message = $this->context->createMessage(json_encode([
            'event' => $eventName,
            'payload' => $payload,
            'timestamp' => time(),
        ]));

        $this->producer->send($queue, $message);
    }
}
```

Ele serializa o evento para JSON e o envia ao RabbitMQ. Nesse ponto, a requisição HTTP está concluída. A API retorna uma resposta ao cliente.

### O Consumer: processando eventos

Enquanto isso, um processo PHP separado roda continuamente:

```php
// bin/consumer.php
$consumer = $container->get(MessageConsumer::class);
echo "Message Consumer started\n";
echo "Listening for events on RabbitMQ...\n";
$consumer->consume();
```

Esse consumer fica ali esperando por mensagens:

```php
public function consume(): void
{
    while (true) {
        $message = $this->consumer->receive(1000);

        if ($message === null) {
            continue;
        }

        try {
            $data = json_decode($message->getBody(), true);
            $event = $data['event'];
            $payload = $data['payload'];

            $this->eventRouter->dispatch($event, $payload);
            $this->consumer->acknowledge($message);

            echo sprintf("Processed event: %s\n", $event);
        } catch (\Exception $e) {
            $this->consumer->reject($message);
            echo sprintf("Error processing event: %s\n", $e->getMessage());
        }
    }
}
```

Quando recebe uma mensagem, ele extrai o nome do evento e o payload, e então o roteia para o listener apropriado.

### O Event Listener: atualizando o MongoDB

O OrderCreatedListener trata o evento:

```php
class OrderCreatedListener implements EventListenerInterface
{
    public static function subscribedTo(): string
    {
        return 'order.created';
    }

    public function handle(array $payload): void
    {
        $collection = $this->mongoConnection->getDatabase()->selectCollection('orders');

        $collection->insertOne([
            '_id' => $payload['order_id'],
            'customer_name' => $payload['customer_name'],
            'customer_email' => $payload['customer_email'],
            'items' => $payload['items'],
            'total_amount' => $payload['total_amount'],
            'status' => $payload['status'],
            'created_at' => $payload['created_at'],
            'updated_at' => null,
        ]);
    }
}
```

Ele pega o payload do evento e o grava diretamente no MongoDB. Agora o modelo de leitura está atualizado.

![Atualização no MongoDB](mongodb-update.png)

### O fluxo completo

Deixa eu juntar tudo:

**Passo 1:** O cliente envia uma requisição POST para criar um pedido
**Passo 2:** O CreateOrderHandler recebe o CreateOrderCommand
**Passo 3:** O handler cria a entidade Order e a salva no PostgreSQL
**Passo 4:** O handler dispara o evento OrderCreated
**Passo 5:** O RabbitMQEventPublisher captura o evento e envia ao RabbitMQ
**Passo 6:** A API responde ao cliente (pedido criado)
**Passo 7:** O consumer captura a mensagem do RabbitMQ
**Passo 8:** O consumer roteia o evento para o OrderCreatedListener
**Passo 9:** O listener insere o pedido no MongoDB
**Passo 10:** O modelo de leitura está agora atualizado

Quando alguém consulta pedidos, ele acessa um endpoint diferente que lê diretamente do MongoDB. Sem PostgreSQL envolvido. Sem eventos. Apenas uma consulta simples.

![Consultando no MongoDB](query-mongodb.png)

### Configuração da injeção de dependências

Tudo é conectado no arquivo de dependências:

```php
EventDispatcher::class => function (ContainerInterface $c) {
    $dispatcher = new EventDispatcher();
    $dispatcher->subscribeTo('order.created', $c->get(RabbitMQEventPublisher::class));
    $dispatcher->subscribeTo('order.updated', $c->get(RabbitMQEventPublisher::class));
    return $dispatcher;
},

CreateOrderHandler::class => function (ContainerInterface $c) {
    return new CreateOrderHandler(
        $c->get(PostgresOrderRepository::class),
        $c->get(EventDispatcher::class)
    );
},
```

O container monta todas as peças e injeta as dependências onde necessário. Command handlers recebem repositórios e event dispatchers. Query handlers recebem conexões com o MongoDB. O consumer recebe o event router com todos os listeners registrados.

### O que faz isso funcionar

A separação é limpa. Comandos não sabem nada sobre o MongoDB. Consultas não sabem nada sobre o PostgreSQL ou os eventos. O sistema de eventos os conecta sem acoplá-los.

Você pode testar cada peça independentemente. Faça mock do repositório para testar o command handler. Faça mock do MongoDB para testar o query handler. O consumer pode ser testado separadamente da API.

E você pode escalar cada peça de forma diferente. Precisa de mais capacidade na API? Adicione mais containers PHP. Precisa processar eventos mais rápido? Rode múltiplas instâncias do consumer. O MongoDB sob pressão? Adicione réplicas. Todos escalam de forma independente.

## Conclusão

CQRS não é uma bala de prata. Ele adiciona complexidade que você precisa justificar com benefícios reais. A maioria das aplicações não precisa dele.

Mas quando você precisa, quando suas leituras e escritas têm necessidades fundamentalmente diferentes, quando você precisa escalá-las de forma independente, o CQRS te dá uma maneira limpa de lidar com essa complexidade.

O código deste projeto mostra que implementar CQRS não precisa ser complicado. Comandos escrevem no PostgreSQL. Consultas leem do MongoDB. Eventos os mantêm em sincronia através do RabbitMQ. Cada peça faz uma coisa bem.

A verdadeira lição é saber quando usar padrões como esse. Não recorra ao CQRS porque ele é interessante ou porque você leu sobre ele em um artigo. Use-o quando tiver os problemas específicos que ele resolve.

O projeto está disponível no GitHub se você quiser se aprofundar. Clone, rode, quebre, veja como funciona. O entendimento vem da prática.

<https://github.com/sahdoio/event-driven-cqrs-php>

## Referências

- Young, G. (2010). *CQRS Documents*. <https://cqrs.wordpress.com/wp-content/uploads/2010/11/cqrs_documents.pdf>
- Fowler, M. (2011). *CQRS*. martinfowler.com. <https://martinfowler.com/bliki/CQRS.html>
- Microsoft. (n.d.). *Command and Query Responsibility Segregation (CQRS) pattern*. Azure Architecture Center. <https://learn.microsoft.com/en-us/azure/architecture/patterns/cqrs>
- Vernon, V. (2013). *Implementing Domain-Driven Design*. Addison-Wesley Professional.
