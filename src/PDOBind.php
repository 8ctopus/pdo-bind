<?php

declare(strict_types=1);

namespace Oct8pus\PDOBind;

use PDO;

class PDOBind
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
     * Factory
     *
     * @param string      $dsn
     * @param null|string $username
     * @param null|string $password
     * @param null|array  $options
     *
     * @return self
     */
    public static function factory(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null) : self
    {
        return new self(new PDO($dsn, $username, $password, $options));
    }

    /**
     * Override PDO prepare method
     *
     * @param string       $query
     * @param array<mixed> $options
     *
     * @return false|PDOStatementBind
     */
    public function prepare(string $query, array $options = []) : false|PDOStatementBind
    {
        $result = $this->db->prepare($query, $options);

        if (!$result) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return new PDOStatementBind($result);
    }

    /**
     * Override PDO query method
     *
     * @param string $query
     * @param mixed  $vars
     *
     * @return false|PDOStatementBind
     */
    public function query(string $query, mixed ...$vars) : false|PDOStatementBind
    {
        $result = $this->db->query($query, ...$vars);

        if (!$result) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return new PDOStatementBind($result);
    }
}
