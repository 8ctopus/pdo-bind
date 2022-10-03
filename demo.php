<?php

use Oct8pus\PDOFix\PDOFix as PDO;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$params = [
    'host' => 'localhost',
    'database' => 'test',
    'user' => 'root',
    'pass' => '123',
];

$db = new PDO("mysql:host={$params['host']};dbname={$params['database']};charset=utf8", $params['user'], $params['pass'], [
    // use exceptions
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // get arrays
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // better prevention against SQL injections
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$sql = <<<SQL
    DROP TABLE IF EXISTS test
SQL;

$query = $db->prepare($sql);
$query->execute();

$sql = <<<SQL
    CREATE TABLE test (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `birthday` DATE NOT NULL,
        `name` VARCHAR(40) NOT NULL,
        `salary` INT NOT NULL,
        `boss` BIT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

$query = $db->prepare($sql);
$query->execute();

$sql = <<<SQL
    INSERT INTO test
    (birthday, name, salary, boss)
    VALUES (:birthday, :name, :salary, :boss)
SQL;

$query = $db->prepare($sql);

$staff = [
    [
        'birthday' => (new DateTime('1995-05-01'))->format('Y-m-d'),
        'name' => 'Sharon',
        'salary' => '200',
        'boss' => true,
    ],
    [
        'birthday' => (new DateTime('2000-01-01'))->format('Y-m-d'),
        'name' => 'John',
        'salary' => '140',
        'boss' => false,
    ],
    [
        'birthday' => (new DateTime('1985-08-01'))->format('Y-m-d'),
        'name' => 'Oliver',
        'salary' => '120',
        'boss' => false,
    ],
];

foreach ($staff as $member) {
    $query->execute($member);
}

$sql = <<<SQL
    SELECT *
    FROM test
SQL;

$query = $db->prepare($sql);
$query->execute();

while ($row = $query->fetch()) {
    echo "{$row['id']} {$row['birthday']} {$row['name']} {$row['salary']} {$row['boss']}\n";
}
