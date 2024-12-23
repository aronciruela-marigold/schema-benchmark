<?php

require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\Command;

$client = new Client("mongodb://mongo:27017");
$database = null;

try {
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "Pinged your deployment. You successfully connected to MongoDB!" . PHP_EOL . PHP_EOL;
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

$database = $client->selectDatabase('test_db_benchmark');

foreach ($collections as $collection => $options) {
    echo "Creating collection $collection" . PHP_EOL;
    $commandCreateCollection = new Command(array_merge(['create' => $collection], $options));

    try {
        $database->command($commandCreateCollection);

        echo "Creating indexes $collection" . PHP_EOL . PHP_EOL;
        $collection = $database->selectCollection($collection);
        $collection->createIndexes([
            ['key' => ['username' => 1]],
            ['key' => ['email' => 1]]
        ]);

    } catch (MongoDB\Driver\Exception\Exception $e) {
        echo "ERROR! $e" . PHP_EOL;
    }
}

$numDocs = $argv[1] == null ? 1000 : $argv[1];
$iterations = $argv[2] == null ? 3 : $argv[2];;

$latencies = [];
$results = [];

echo "Running CRUD benchmark with $numDocs Documents in $iterations Iterations" . PHP_EOL;

for ($i = 1; $i <= $iterations; $i++) {
    echo "# ITERATION $i" .  PHP_EOL;
    $results["iteration-{$i}"] = [];

    foreach (array_keys($collections) as $collection) {
        echo "## For collection $collection" . PHP_EOL;
        $startTime = microtime(true);

        $insertStartTime = microtime(true);
        for ($x = 0; $x < $numDocs; $x++) {
            $database->selectCollection($collection)->insertOne([
                'name' => "User {$x}",
                'email' => "user{$x}@example.com"
            ]);
        }
        $insertEndTime = microtime(true);
        $totalExecTime = $insertEndTime - $insertStartTime;
        echo "### Insert Exec Time: " . number_format($totalExecTime, 2) . 's' . PHP_EOL;

        $findStartTime = microtime(true);
        for ($x = 0; $x < $numDocs; $x++) {
            $database->selectCollection($collection)->findOne(['name' => "User {$x}"]);
        }
        $findEndTime = microtime(true);
        $totalExecTime = $findEndTime - $findStartTime;
        echo "### Find Exec Time: " . number_format($totalExecTime, 2) . 's' . PHP_EOL;

        $updateStartTime = microtime(true);
        for ($x = 0; $x < $numDocs; $x++) {
            $database->selectCollection($collection)->updateOne(
                ['name' => "User {$x}"],
                ['$set' => ['email' => "updated{$x}@example.com"]]
            );
        }
        $updateEndTime = microtime(true);
        $totalExecTime = $updateEndTime - $updateStartTime;
        echo "### Update Exec Time: " . number_format($totalExecTime, 2) . 's' . PHP_EOL;

        $deleteStartTime = microtime(true);
        for ($x = 0; $x < $numDocs; $x++) {
            $database->selectCollection($collection)->deleteOne(['name' => "User {$x}"]);
        }
        $deleteEndTime = microtime(true);
        $totalExecTime = $deleteEndTime - $deleteStartTime;
        echo "### Delete Exec Time: " . number_format($totalExecTime, 2) . 's' . PHP_EOL;

        $endTime = microtime(true);
        $totalExecTime = $endTime - $startTime;
        $results["iteration-{$i}"][$collection] = $totalExecTime;
        echo "### Execution Time: " . number_format($totalExecTime, 2) . 's' . PHP_EOL . PHP_EOL;
    }

    $collections = array_reverse($collections);
}

echo "# TOTAL in $iterations ITERATIONS" . PHP_EOL;

$collectionResult = [];

foreach (array_keys($collections) as $collection) {
    $collectionResult[$collection] = [];

    foreach ($results as $iterations) {
        foreach ($iterations as $coll => $latency) {
            if($collection == $coll) {
                array_push($collectionResult[$collection], $latency);
            }
        }
    }
}

foreach ($collectionResult as $coll => $latencies) {
    echo "## Collection $coll" . PHP_EOL;
    $totalLatencies = array_sum($latencies);
    $mean = $totalLatencies / count($latencies);

    $variance = 0.0;
    foreach($latencies as $latency) {
        $variance += pow(($latency - $mean), 2);
    }
    $standardDeviation = (float)sqrt( $variance /count($latencies));

    echo "### Total execution time " . number_format($totalLatencies, 2) . 's' . PHP_EOL;
    echo "### Average " . number_format($mean, 2) . 's' . PHP_EOL;
    echo "### Standard Deviation " . number_format($standardDeviation, 2) . 's' . PHP_EOL;
    echo PHP_EOL;
}

$database->drop();
