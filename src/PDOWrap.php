<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use PDO;

class PDOWrap
{
    private readonly PDO $pdo;

    /**
     * Constructor
     *
     * @param string  $dsn
     * @param ?string $username
     * @param ?string $password
     * @param ?array<string>  $options
     */
    public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
    {
        $this->pdo = new PDO($dsn, $username, $password, $options);
    }

    /**
     * Call PDO method
     *
     * @param string $method
     * @param array<mixed>  $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args) : mixed
    {
        return $this->pdo->{$method}(...$args);
    }

    /**
     * Override PDO prepare method
     *
     * @param string $query
     * @param array<mixed>  $options
     * @param bool   $convert
     *
     * @return false|PDOStatementWrap
     */
    public function prepare(string $query, array $options = [], bool $convert = false) : false|PDOStatementWrap
    {
        $result = $this->pdo->prepare($query, $options);

        if (!$result) {
            return false;
        }

        return new PDOStatementWrap($result, $convert);
    }

    /**
     * Override PDO query method
     *
     * @param string $query
     * @param mixed $vars
     *
     * @return false|PDOStatementWrap
     */
    public function query(string $query, mixed ...$vars) : false|PDOStatementWrap
    {
        $result = $this->pdo->query($query, ...$vars);

        if (!$result) {
            return false;
        }

        return new PDOStatementWrap($result, false);
    }
}
