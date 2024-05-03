<?php

declare(strict_types=1);

namespace Tests;

use DateTime;
use Oct8pus\PDOBind\Date;
use Oct8pus\PDOBind\PDOBind;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PDOBind\PDOStatementBind
 * @covers \Oct8pus\PDOBind\PDOBind
 */
final class PDOBindTest extends TestCase
{
    private static PDOBind $db;

    public static function setUpBeforeClass() : void {}

    public function testConstructor() : void
    {
        $db = new PDOBind(new PDO(...Database::get()));

        self::assertInstanceOf(PDOBind::class, $db);
    }

    public function testFactory() : void
    {
        self::$db = PDOBind::factory(...Database::get());

        self::assertInstanceOf(PDOBind::class, self::$db);
    }

    public function testDatabaseExec() : void
    {
        $sql = <<<'SQL'
        DROP TABLE IF EXISTS `test`;

        CREATE TABLE `test` (
            `id` INTEGER PRIMARY KEY AUTO_INCREMENT,
            `birthday` DATE NOT NULL,
            `name` VARCHAR(40) NOT NULL,
            `salary` INTEGER NOT NULL,
            `boss` BIT NOT NULL,
            `lastSeen` DATETIME NULL,
            `comment` VARCHAR(40) NULL
        )
        SQL;

        if ($_ENV['DB_ENGINE'] === 'sqlite') {
            $sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql);
        }

        $result = self::$db->exec($sql);

        self::assertSame(0, $result);
    }

    public function testQueries() : void
    {
        $sql = <<<'SQL'
        INSERT INTO `test`
            (`birthday`, `name`, `salary`, `boss`, `lastSeen`, `comment`)
        VALUES
            (:birthday, :name, :salary, :boss, :lastSeen, :comment)
        SQL;

        $query = self::$db->prepare($sql);

        $staff = [
            [
                'birthday' => new Date('1995-05-01'),
                'name' => 'Sharon',
                'salary' => 200,
                'boss' => true,
                'lastSeen' => new DateTime('2021-10-08 12:00:00'),
                'comment' => "She's the boss",
            ],
            [
                'birthday' => new Date('2000-01-01'),
                'name' => 'John',
                'salary' => 140,
                'boss' => false,
                'lastSeen' => new DateTime('2023-10-08 12:00:00'),
                'comment' => null,
            ],
            [
                'birthday' => new Date('1985-08-01'),
                'name' => 'Oliver',
                'salary' => 120,
                'boss' => false,
                'lastSeen' => new DateTime('2024-10-08 12:00:00'),
                'comment' => null,
            ],
        ];

        foreach ($staff as $member) {
            $query->execute($member);
        }

        $sql = <<<'SQL'
        SELECT
            `birthday`, `name`, `salary`, `boss`
        FROM
            `test`
        SQL;

        $query = self::$db->query($sql);
        $result = $query->fetchAll();

        self::assertSame([
            [
                'birthday' => '1995-05-01',
                'name' => 'Sharon',
                'salary' => 200,
                'boss' => 1,
            ], [
                'birthday' => '2000-01-01',
                'name' => 'John',
                'salary' => 140,
                'boss' => 0,
            ], [
                'birthday' => '1985-08-01',
                'name' => 'Oliver',
                'salary' => 120,
                'boss' => 0,
            ],
        ], $result);
    }

    public function testDatesQuery() : void
    {
        $sql = <<<'SQL'
        SELECT
            `birthday`, `name`
        FROM
            `test`
        WHERE
            `lastSeen` BETWEEN :from AND :to
        SQL;

        $query = self::$db->prepare($sql);
        $query->execute([
            'from' => new DateTime('2021-10-08 11:00:00'),
            'to' => new DateTime('2021-10-08 14:00:00'),
        ]);

        $record = $query->fetchObject();

        $expected = [
            'birthday' => '1995-05-01',
            'name' => 'Sharon',
        ];

        self::assertEquals((object) $expected, $record);

        $query = self::$db->prepare($sql);
        $query->execute([
            'from' => new DateTime('2021-10-08'),
            'to' => new DateTime('2021-10-08'),
        ]);

        $name = $query->fetchColumn(1);
        self::assertSame(false, $name);

        $sql = <<<'SQL'
        SELECT
            `birthday`, `name`
        FROM
            `test`
        WHERE
            DATE(`lastSeen`) BETWEEN :from AND :to
        SQL;

        $query = self::$db->prepare($sql);
        $query->execute([
            'from' => new Date('2021-10-08'),
            'to' => new Date('2021-10-08'),
        ]);

        $row = $query->fetch();
        self::assertSame($expected, $row);
    }
}
