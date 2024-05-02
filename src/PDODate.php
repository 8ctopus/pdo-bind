<?php

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;

class PDODate extends DateTime
{
    public function __toString() : string
    {
        return $this->format('Y-m-d');
    }
}
