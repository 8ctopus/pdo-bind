<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use PDO;

class PDOWrap extends PDO
{
    public function prepare(string $query, array $options = []) : false|PDOStatementFix
    {
        $result = parent::prepare($query, $options);

        if (!$result) {
            return false;
        }

        return new PDOStatementFix($result);
    }
}
