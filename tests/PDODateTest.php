<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PDOWrap\PDOStatementWrap
 * @covers \Oct8pus\PDOWrap\PDODate
 */
class PDODateTest extends TestCase
{
    public function test() : void
    {
        $date = new PDODate('2021-10-08');

        self::assertInstanceOf(DateTime::class, $date);
        self::assertSame('2021-10-08', (string) $date);
    }
}
