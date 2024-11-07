# php xml-rpc client

## intro

xml-rpc client for php 8.3.

it builds on top of `symfony/serializer` and `psr/http`.

## usage

```php
$client = new Client(
    // ...
    onRequest: static fn ($request) => $request // psr request
            ->withUri($request->getUri()->withScheme('http')->withHost('betty.userland.com')->withPath('/RPC2'))
);

// alternatively, override Client::onRequest() instead of using $onRequest argument

$response = $client->sendRpcRequest($client->createRpcRequest(method: 'examples.echoParams', params: ['hello', 'world']));
dump([...$response]);
/*
array:1 [▼
  0 => array:2 [▼
    0 => "hello"
    1 => "world"
  ]
]
*/

// params is an iterable, so with Generator
$response = $client->sendRpcRequest($client->createRpcRequest(method: 'examples.echoParams', params: (function () {
    yield 'start';

    for ($i = 0; $i < 10; ++$i) {
        yield 'item'.$i;
    }

    yield new \DateTimeImmutable('2024-01-01T00:00:00Z');
    yield 'end';
})()));
dump([...$response]);

/*
array:1 [▼
  0 => array:12 [▼
    0 => "start"
    1 => "item0"
    2 => "item1"
    3 => "item2"
    4 => "item3"
    5 => "item4"
    6 => "item5"
    7 => "item6"
    8 => "item7"
    9 => "item8"
    10 => "item9"
    11 => DateTimeImmutable @1704085200 {#22469 ▼
      date: 2024-01-01 05:00:00.0 +00:00
    }
    12 => "end"
  ]
]
 */

// control the type by wrapping it with DataValue
$response = $client->sendRpcRequest($client->createRpcRequest('examples.echoParams', [[
    'as string' => new DataValue(123.456, DataType::STRING),
    'as integer' => new DataValue(123.456, DataType::INTEGER),
    'as float' => new DataValue(123.456, DataType::DOUBLE),
]]));
dump([...$response]);

/*
array:1 [▼
  0 => array:1 [▼
    0 => array:3 [▼
      "as string" => "123.456"
      "as integer" => 123
      "as float" => 123.456
    ]
  ]
] 
 */
```

## internals

data values are _normalized_ to `DataValue` and _denormalized_ to php variables or xml-rpc values.

`PhpNormalizer` and `PhpDenormalizer` also perform data conversion, eg. `base64_encode/decode`, format/create `DateTimeInterface` etc.

`RpcNormalizer` and `RpcDenormalizer` don't do that.
