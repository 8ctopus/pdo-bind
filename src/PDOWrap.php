<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use PDO;

class PDOWrap
{
    private readonly PDO $pdo;
    private readonly bool $convert;

    /**
     * Constructor
     *
     * @param string  $dsn
     * @param ?string $username
     * @param ?string $password
     * @param ?array  $options
     * @param bool    $convert
     */
    public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null, bool $convert = false)
    {
        $this->pdo = new PDO($dsn, $username, $password, $options);
        $this->convert = $convert;
    }

    /**
     * Call PDO method
     *
     * @param  string $method
     * @param  array  $args
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
     * @param  string $query
     * @param  array  $options
     *
     * @return PDOStatementWrap|false
     */
    public function prepare(string $query, array $options = []) : false|PDOStatementWrap
    {
        $result = $this->pdo->prepare($query, $options);

        if (!$result) {
            return false;
        }

        return new PDOStatementWrap($result, $this->convert);
    }

    /**
     * Override PDO query method
     *
     * @param  string $query
     * @param  [type] $vars
     *
     * @return PDOStatementWrap|false
     */
    public function query(string $query, ...$vars) : false|PDOStatementWrap
    {
        $result = $this->pdo->query($query, ...$vars);

        if (!$result) {
            return false;
        }

        return new PDOStatementWrap($result, $this->convert);
    }
}
