<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;
use Exception;
use PDO;
use PDOStatement;

class PDOStatementWrap
{
    private readonly PDOStatement $statement;
    private readonly bool $convert;

    /**
     * Constructor
     *
     * @param PDOStatement $statement
     * @param bool $convert
     */
    public function __construct(PDOStatement $statement, bool $convert)
    {
        $this->statement = $statement;
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
        return $this->statement->{$method}(...$args);
    }

    /**
     * Override PDO execute method
     *
     * @param  array|null $params
     *
     * @return bool
     */
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
                return PDO::PARAM_INT;

            case 'boolean':
                return PDO::PARAM_BOOL;

            case 'NULL':
                return PDO::PARAM_NULL;

            default:
                throw new Exception("unsupported type - {$type}");
        }
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0) : mixed
    {
        $row = $this->statement->fetch($mode, $cursorOrientation, $cursorOffset);

        if ($row === false || $this->convert === false) {
            return $row;
        }

        return $this->convertColumns([$row])[0];
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, ...$args) : array|false
    {
        $rows = $this->statement->fetchAll($mode, ...$args);

        if ($rows === false || $this->convert === false) {
            return $rows;
        }

        return $this->convertColumns($rows);
    }

    public function fetchColumn(int $column = 0) : mixed
    {
        $value = $this->statement->fetchColumn($column);

        if ($value === false || $this->convert === false) {
            return $value;
        }

        $meta = $this->meta();

        $type = $meta[$column]['type'];

        switch ($type) {
            case 'BIT':
                $value = (bool) $value;
                break;

            case 'DATE':
                $value = DateTime::createFromFormat('Y-m-d', $value);
                break;

            case 'DATETIME':
                $value = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                break;

            default:
                throw new Exception("unknown column type - {$type}");
        }

        return $value;
    }

    /**
     * Convert columns type
     *
     * @param  array $rows
     *
     * @return array
     */
    private function convertColumns(array $rows) : array
    {
        $columnsCount = $this->statement->columnCount();

        $meta = $this->meta();

        $rowsCount = count($rows);

        for ($column = 0; $column < $columnsCount; ++$column) {
            $type = $meta[$column]['type'];
            $name = $meta[$column]['name'];

            // conversion not needed
            if (in_array($type, ['INT', 'INTEGER', 'LONG', 'VARCHAR(40)', 'VAR_STRING'])) {
                continue;
            }

            for ($row = 0; $row < $rowsCount; ++$row) {
                switch ($type) {
                    case 'BIT':
                        $rows[$row][$name] = (bool) $rows[$row][$name];
                        break;

                    case 'DATE':
                        $rows[$row][$name] = DateTime::createFromFormat('Y-m-d', $rows[$row][$name]);
                        break;

                    case 'DATETIME':
                        $rows[$row][$name] = DateTime::createFromFormat('Y-m-d H:i:s', $rows[$row][$name]);
                        break;

                    default:
                        throw new Exception("unknown column type - {$type}");
                }
            }
        }

        return $rows;
    }

    /**
     * Get column meta
     *
     * @return array
     */
    private function meta() : array
    {
        $columnsCount = $this->statement->columnCount();

        $metas = [];

        for ($i = 0; $i < $columnsCount; $i++) {
            $meta = $this->statement->getColumnMeta($i);

            $meta['type'] = $meta['sqlite:decl_type'] ?? $meta['mysql:decl_type'] ?? $meta['native_type'];

            $metas[] = $meta;
        }

        return $metas;
    }
}
