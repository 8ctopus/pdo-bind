<?php declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PDOWrap\PDOWrap
 * @covers \Oct8pus\PDOWrap\PDOStatementWrap
 */
final class PDOWrapTest extends TestCase
{
    private static PDOWrap $db;

    public static function setUpBeforeClass(): void
    {
    }

    public function testConstructor() : void
    {
        self::$db = new PDOWrap('sqlite::memory:', null, null, [
            // use exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // get arrays
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // better prevention against SQL injections
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::assertTrue(true);
    }

    public function testQuery() : void
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

        self::$db->query($sql);

        self::assertTrue(true);
    }

    public function testPrepare() : void
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
                'birthday' => (new DateTime('1995-05-01'))->format('Y-m-d'),
                'name' => 'Sharon',
                'salary' => 200,
                'boss' => true,
            ],
            [
                'birthday' => (new DateTime('2000-01-01'))->format('Y-m-d'),
                'name' => 'John',
                'salary' => 140,
                'boss' => false,
            ],
            [
                'birthday' => (new DateTime('1985-08-01'))->format('Y-m-d'),
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
}
