<?php

declare(strict_types=1);

use NunoMaduro\Collision\Provider;
use Oct8pus\PDOBind\Date;
use Oct8pus\PDOBind\PDOBind;
use Tests\Database;

require_once __DIR__ . '/vendor/autoload.php';

(new Provider())
    ->register();

echo 'select database (sqlite,mysql): ';
$input = fgets(STDIN);

$database = $input === 'mysql' ? [
    "mysql:host=localhost;dbname=test",
    $user,
    $pass,
] : [
    'sqlite::memory:',
    null,
    null,
];

$database[] = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$db = PDOBind::factory(...$database);

$sql = <<<'SQL'
DROP TABLE IF EXISTS `test`
SQL;

$db->exec($sql);

$sql = <<<'SQL'
CREATE TABLE `test` (
    `id` INTEGER PRIMARY KEY AUTO_INCREMENT,
    `birthday` DATE NOT NULL,
    `name` VARCHAR(40) NOT NULL,
    `salary` INT NOT NULL,
    `boss` BIT NOT NULL
)
SQL;

if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
    $sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql);
}

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
        'birthday' => new Date('1995-05-01'),
        'name' => 'Sharon',
        'salary' => 200,
        'boss' => true,
    ],
    [
        'birthday' => new Date('2000-01-01'),
        'name' => 'John',
        'salary' => 140,
        'boss' => false,
    ],
    [
        'birthday' => new Date('1985-08-01'),
        'name' => 'Oliver',
        'salary' => 120,
        'boss' => false,
    ],
];

foreach ($staff as $member) {
    $query->execute($member);
}

$sql = <<<'SQL'
SELECT
    `id`, `birthday`, `name`, `salary`, `boss`
FROM
    `test`
SQL;

$query = $db->prepare($sql);
$query->execute();

while ($row = $query->fetch()) {
    echo "{$row['id']} {$row['birthday']} {$row['name']} {$row['salary']} {$row['boss']}\n";
}

$query = $db->prepare($sql);
$query->execute();

$rows = $query->fetchAll();

foreach ($rows as $row) {
    echo "{$row['id']} {$row['birthday']} {$row['name']} {$row['salary']} {$row['boss']}\n";
}

$query = $db->prepare($sql);
$query->execute();

$birthday = $query->fetchColumn(1);

echo "{$birthday}\n";
