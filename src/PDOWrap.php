<?php

namespace Oct8pus\PDOWrap;

use PDO;

class PDOWrap extends PDO
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
