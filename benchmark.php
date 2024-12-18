<?php

require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\Command;
use MongoDB\Driver\BulkWrite;
use MongoDB\Database;

$client = new Client("mongodb://mongo:27017");
$database = null;

try {
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "Pinged your deployment. You successfully connected to MongoDB!" . PHP_EOL;
} catch (Exception $e) {
    printf($e->getMessage());
}

echo 'Running Schemas Benchmark' . PHP_EOL;

$collections = [
    "no_validation" => [],
    "with_validation" => [
        "validator" => [
            '$jsonSchema' => [
                'bsonType' => 'object',
                'required' => ['name', 'email'],
                'properties' => [
                    'name' => ['bsonType' => 'string'],
                    'email' => ['bsonType' => 'string']
                ]
            ]
        ],
        'validationLevel' => 'strict',
        'validationAction' => 'error'
    ]
];

foreach ($collections as $collection => $options) {
    echo "Creating collection $collection" . PHP_EOL;
    $command = new Command(array_merge([
        'create' => $collection
    ], $options));

    try {
        $database = $client->selectDatabase('test_db_benchmark');
        $database->command($command);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        printf($e->getMessage());
        echo "ERROR! $e" . PHP_EOL;
    }
}

function benchmarkCollections(Database $database, $collection, $numDocs) {
    $startTime = microtime(true);

    $startWriteTime = microtime(true);
    for ($i = 0; $i < $numDocs; $i++) {
        $insert = new Command([
            'insert' => $collection,
            'documents' => [
                [
                    'name' => "User {$i}",
                    'email' => "user{$i}@example.com"
                ]
            ]
        ]);
        $database->command($insert);
    }
    $endWriteTime = microtime(true);
    $writeLatency = $endWriteTime - $startWriteTime;
    echo "Collection: $collection Documents inserted: $i write time latency: " . number_format($writeLatency, 4) . PHP_EOL;

    $startUpdateTime = microtime(true);
    for ($i = 0; $i < $numDocs; $i++) {
        $update = new Command([
            'update' => $collection,
            'updates' => [
                [
                    'q' => ['name' => "User {$i}"],
                    'u' => ['$set' => ['email' => "updated{$i}@example.com"]],
                    'upsert' => false
                ]
            ]
        ]);
        $database->command($update);
    }
    $endUpdateTime = microtime(true);
    $updateLatency = $endUpdateTime - $startUpdateTime;
    echo "Collection: $collection Documents updated: $i update time latency: " . number_format($updateLatency, 4) . PHP_EOL;

    $endTime = microtime(true);

    return $endTime - $startTime;
}

$numDocs = 10000;
$latencies = [];

foreach (array_keys($collections) as $collection) {
    echo "running bechmark for collection $collection ============>" . PHP_EOL;
    $latencies[$collection] = benchmarkCollections($database, $collection, $numDocs);
}

echo '--------------------------' . PHP_EOL;

foreach ($latencies as $collection => $latency) {
    echo "Collection: $collection, total latency: " . number_format($latency, 4) . " seconds" . PHP_EOL;
}

$database->drop();
