<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use PDO;

class PDOWrap
{
    private readonly PDO $db;

    /**
     * Constructor
     *
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Factory
     *
     * @param  string      $dsn
     * @param  string|null $username
     * @param  string|null $password
     * @param  array|null  $options
     *
     * @return self
     */
    public static function factory(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null) : self
    {
        return new self(new PDO($dsn, $username, $password, $options));
    }

    /**
     * Call PDO method
     *
     * @param string       $method
     * @param array<mixed> $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args) : mixed
    {
        return $this->db->{$method}(...$args);
    }

    /**
     * Override PDO prepare method
     *
     * @param string       $query
     * @param array<mixed> $options
     *
     * @return false|PDOStatementWrap
     */
    public function prepare(string $query, array $options = []) : false|PDOStatementWrap
    {
        $result = $this->db->prepare($query, $options);

        if (!$result) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return new PDOStatementWrap($result);
    }

    /**
     * Override PDO query method
     *
     * @param string $query
     * @param mixed  $vars
     *
     * @return false|PDOStatementWrap
     */
    public function query(string $query, mixed ...$vars) : false|PDOStatementWrap
    {
        $result = $this->db->query($query, ...$vars);

        if (!$result) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return new PDOStatementWrap($result);
    }
}
