<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;
use Exception;
use PDO;

class PDOStatementWrapAdvanced extends PDOStatementWrap
{
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0) : mixed
    {
        $row = $this->statement->fetch($mode, $cursorOrientation, $cursorOffset);

        if ($row === false) {
            return false;
        }

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
}
