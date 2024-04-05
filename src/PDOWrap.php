<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use PDO;

class PDOWrap
{
    private PDO $pdo;

    public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
    {
        $this->pdo = new PDO($dsn, $username, $password, $options);
    }

    public function prepare(string $query, array $options = []) : false|PDOStatementWrap
    {
        $result = $this->pdo->prepare($query, $options);

        if (!$result) {
            return false;
        }

        return new PDOStatementWrap($result);
    }

    public function query(string $query, ...$vars) : false|PDOStatementWrap
    {
        $result = $this->pdo->query($query, ...$vars);

        if (!$result) {
            return false;
        }

        return new PDOStatementWrap($result);
    }

    public function __call(string $method, array $args) : mixed
    {
        return $this->pdo->{$method}(...$args);
    }
}
