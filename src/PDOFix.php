<?php

namespace Oct8pus\PDOFix;

use Exception;
use PDO;
use PDOStatement;

class PDOStatementFix extends PDOStatement
{
    private PDOStatement $statement;

    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
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

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->statement->fetch($mode, $cursorOrientation, $cursorOffset);
    }

    /**
     * Variable to PDO type
     *
     * @param  mixed  $value
     *
     * @return int PDO type
     */
    private function typeToParam(mixed $value) : int
    {
        switch ($type = gettype($value)) {
            case 'boolean':
                return PDO::PARAM_BOOL;

            case 'integer':
                return PDO::PARAM_INT;

            case 'NULL':
                return PDO::PARAM_NULL;

            case 'string':
                return PDO::PARAM_STR;

            default:
                throw new Exception("unsupported type - {$type}");
        }
    }
}

class PDOFix extends PDO
{
    public function prepare(string $query, array $options = []) : PDOStatementFix|false
    {
        $result = parent::prepare($query, $options);

        if (!$result) {
            return false;
        }

        return new PDOStatementFix($result);
    }
}
