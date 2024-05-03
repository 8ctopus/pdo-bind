<?php

declare(strict_types=1);

namespace Tests;

use DateTime;
use Oct8pus\PDOBind\Date;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PDOBind\PDOStatementBind
 * @covers \Oct8pus\PDOBind\Date
 */
class DateTest extends TestCase
{
    public function test() : void
    {
        $date = new Date('2021-10-08');

        self::assertInstanceOf(DateTime::class, $date);
        self::assertSame('2021-10-08', (string) $date);
    }
}
