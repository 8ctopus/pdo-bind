<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use Exception;
use PDO;
use PDOStatement;

class PDOStatementWrap
{
    private PDOStatement $statement;

    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function __call(string $method, array $args) : mixed
    {
        return $this->statement->{$method}(...$args);
    }

    public function execute(?array $params = null) : bool
    {
        if ($params) {
            foreach ($params as $key => $value) {
                $this->statement->bindValue($key, $value, $this->typeToParam($value));
            }
        }

        return $this->statement->execute();
    }

    /**
     * Variable to PDO type
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
                return PDO::PARAM_INT;

            case 'boolean':
                return PDO::PARAM_BOOL;

            case 'NULL':
                return PDO::PARAM_NULL;

            default:
                throw new Exception("unsupported type - {$type}");
        }
    }
}
