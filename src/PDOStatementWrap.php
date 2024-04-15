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

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, ...$args) : array
    {
        $rows = $this->statement->fetchAll($mode, ...$args);

        if ($rows === false || $this->convert === false) {
            return $rows;
        }

        return $this->convertColumns($rows);
    }

    /**
     * Convert rows
     *
     * @param  array $rows
     *
     * @return array
     */
    /*
    private function convertRows(array $rows) : array
    {
        foreach ($rows as &$row) {
            $row = $this->convertRow($row);
        }

        return $rows;
    }
    */

    /**
     * Convert row
     *
     * @param  array $row
     *
     * @return array
     */
    /*
    private function convertRow(array $row) : array
    {
        for ($i = 0; $i < $this->statement->columnCount(); $i++) {
            $metas[] = $this->statement->getColumnMeta($i);
        }

        $i = 0;

        foreach ($row as &$cell) {
            $type = $metas[$i]['sqlite:decl_type'];

            switch ($type) {
                case 'DATE':
                    $cell = DateTime::createFromFormat('Y-m-d', $cell);
                    break;

                case 'DATETIME':
                    $cell = DateTime::createFromFormat('Y-m-d H:i:s', $cell);
                    break;

                case 'BIT':
                    $cell = (bool) $cell;
                    break;

                case 'INTEGER':
                case 'INT':
                case 'VARCHAR(40)':
                    break;

                default:
                    throw new Exception("unknown column type - {$type}");
            }

            ++$i;
        }

        return $row;
    }
    */

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

        for ($i = 0; $i < $columnsCount; $i++) {
            $metas[] = $this->statement->getColumnMeta($i);
        }

        $rowsCount = count($rows);

        for ($column = 0; $column < $columnsCount; ++$column) {
            $type = $metas[$column]['sqlite:decl_type'];
            $name = $metas[$column]['name'];

            // conversion not needed
            if (in_array($type, ['INT', 'INTEGER', 'VARCHAR(40)'])) {
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

                ++$i;
            }
        }

        return $rows;
    }
}
