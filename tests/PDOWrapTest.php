<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;
use PDO;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 *
 * @covers \Oct8pus\PDOWrap\PDOStatementWrap
 * @covers \Oct8pus\PDOWrap\PDOWrap
 */
final class PDOWrapTest extends TestCase
{
    private static PDOWrap $db;

    public static function setUpBeforeClass() : void {}

    public function testConstructor() : void
    {
        $db = new PDOWrap(new PDO('sqlite::memory:'));

        self::assertInstanceOf(PDOWrap::class, $db);
    }

    public function testFactory() : void
    {
        self::$db = PDOWrap::factory('sqlite::memory:', null, null, [
            // use exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // get arrays
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // better prevention against SQL injections
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::assertInstanceOf(PDOWrap::class, self::$db);
    }

    public function testDatabaseExec() : void
    {
        $sql = <<<'SQL'
        CREATE TABLE `test` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `birthday` DATE NOT NULL,
            `name` VARCHAR(40) NOT NULL,
            `salary` INTEGER NOT NULL,
            `boss` BIT NOT NULL
        )
        SQL;

        $result = self::$db->exec($sql);

        self::assertSame(0, $result);
    }

    public function testQueries() : void
    {
        $sql = <<<'SQL'
        INSERT INTO `test`
            (`birthday`, `name`, `salary`, `boss`)
        VALUES
            (:birthday, :name, :salary, :boss)
        SQL;

        $query = self::$db->prepare($sql);

        $staff = [
            [
                'birthday' => new PDODate('1995-05-01'),
                'name' => 'Sharon',
                'salary' => 200,
                'boss' => true,
            ],
            [
                'birthday' => new PDODate('2000-01-01'),
                'name' => 'John',
                'salary' => 140,
                'boss' => false,
            ],
            [
                'birthday' => new PDODate('1985-08-01'),
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
            `birthday`, `name`, `salary`, `boss`
        FROM
            `test`
        WHERE
            `birthday` BETWEEN :from AND :to
        SQL;

        $query = self::$db->prepare($sql);
        $query->execute([
            'from' => new PDODate('1995-05-01'),
            'to' => new PDODate('1995-05-01'),
        ]);

        $record = $query->fetchObject();

        $expected = new stdClass();
        $expected->birthday = '1995-05-01';
        $expected->name = 'Sharon';
        $expected->salary = 200;
        $expected->boss = 1;

        self::assertEquals($expected, $record);

        $query = self::$db->prepare($sql);
        $query->execute([
            'from' => new DateTime('1995-05-01'),
            'to' => new DateTime('1995-05-01'),
        ]);

        $row = $query->fetch();

        self::assertSame(false, $row);
    }
}
