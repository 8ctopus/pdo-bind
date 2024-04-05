<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use PDO;

class PDOWrap
{
    private PDO $pdo;

    public function __construct(string $dsn, ?string $user, ?string $pass)
    {
        $this->pdo = new PDO($dsn, $user, $pass);
    }

    public function __call(string $method, array $args) : mixed
    {
        return $this->pdo->{$method}(...$args);
    }

    public function prepare(string $query, array $options = []) : false|PDOStatementWrap
    {
        $result = $this->pdo->prepare($query, $options);

        if (!$result) {
            return false;
        }

        return new PDOStatementWrap($result);
    }
}
