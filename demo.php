<?php

declare(strict_types=1);

use NunoMaduro\Collision\Provider;
use Oct8pus\PDOWrap\PDOWrap;

require_once __DIR__ . '/vendor/autoload.php';

(new Provider())->register();

$db = new PDOWrap('sqlite::memory:', null, null, [
    // use exceptions
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // get arrays
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // better prevention against SQL injections
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$sql = <<<'SQL'
CREATE TABLE `test` (
    `id` INT PRIMARY KEY,
    `birthday` DATE NOT NULL,
    `name` VARCHAR(40) NOT NULL,
    `salary` INT NOT NULL,
    `boss` BIT NOT NULL
)
SQL;

$query = $db->prepare($sql);
$query->execute();

$sql = <<<'SQL'
INSERT INTO `test`
    (`birthday`, `name`, `salary`, `boss`)
VALUES
    (:birthday, :name, :salary, :boss)
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

$sql = <<<'SQL'
SELECT
    *
FROM
    `test`
SQL;

$query = $db->prepare($sql);
$query->execute();

while ($row = $query->fetch()) {
    echo "{$row['id']} {$row['birthday']} {$row['name']} {$row['salary']} {$row['boss']}\n";
}
