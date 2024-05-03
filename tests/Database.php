<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use PDO;

class Database
{
    public static function get() : array
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
}
