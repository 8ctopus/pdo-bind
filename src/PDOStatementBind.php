<?php

declare(strict_types=1);

namespace Oct8pus\PDOBind;

use DateTime;
use Exception;
use PDO;
use PDOStatement;

class PDOStatementBind
{
    private readonly PDOStatement $statement;

    /**
     * Constructor
     *
     * @param PDOStatement $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
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
        return $this->statement->{$method}(...$args);
    }

    /**
     * Override PDO execute method
     *
     * @param ?array<mixed> $params
     *
     * @return bool
     */
    public function execute(?array $params = null) : bool
    {
        if ($params) {
            foreach ($params as $key => $value) {
                if ($value instanceof Date) {
                    $value = $value->format('Y-m-d');
                } elseif ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                $this->statement->bindValue($key, $value, $this->typeToParam($value));
            }
        }

        return $this->statement->execute();
    }

    /**
     * Fetch row
     *
     * @param int $mode
     * @param int $cursorOrientation
     * @param int $cursorOffset
     *
     * @return mixed
     */
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0) : mixed
    {
        return $this->statement->fetch($mode, $cursorOrientation, $cursorOffset);
    }

    /**
     * Fetch all rows
     *
     * @param int   $mode
     * @param mixed ...$args
     *
     * @return array<mixed>|false
     */
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args) : array|false
    {
        return $this->statement->fetchAll($mode, ...$args);
    }

    /**
     * Fetch column
     *
     * @param int $column
     *
     * @return mixed
     */
    public function fetchColumn(int $column = 0) : mixed
    {
        return $this->statement->fetchColumn($column);
    }

    /**
     * Value to PDO type
     *
     * @param mixed $value
     *
     * @return int PDO type
     */
    private function typeToParam(mixed $value) : int
    {
        switch ($type = gettype($value)) {
            case 'string':
                return PDO::PARAM_STR;

            case 'integer':
            case 'double':
                return PDO::PARAM_INT;

            case 'boolean':
                return PDO::PARAM_BOOL;

            case 'NULL':
                return PDO::PARAM_NULL;

            default:
                // @codeCoverageIgnoreStart
                throw new Exception("unsupported type - {$type}");
                // @codeCoverageIgnoreEnd
        }
    }
}
