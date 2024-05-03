<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;
use Exception;
use PDO;
use PHPUnit\Framework\TestCase;

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

    public function database() : array
    {
        switch ($engine = $_ENV['DB_ENGINE']) {
            case 'mysql':
                $args = [
                    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                ];
                break;

            case 'sqlite':
                $args = [
                    'sqlite::memory:',
                    null,
                    null,
                ];
                break;

            default:
                throw new Exception("unsupported database engine {$engine}");
        }

        $args[] = [
            // use exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // get arrays
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // better prevention against SQL injections
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return $args;
    }

    public function testConstructor() : void
    {
        $db = new PDOWrap(new PDO(...$this->database()));

        self::assertInstanceOf(PDOWrap::class, $db);
    }

    public function testFactory() : void
    {
        self::$db = PDOWrap::factory(...$this->database());

        self::assertInstanceOf(PDOWrap::class, self::$db);
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
