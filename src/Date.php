<?php

declare(strict_types=1);

namespace Oct8pus\PDOBind;

use DateTime;

// php does not distinguish between a date and a date time hence this class
class Date extends DateTime
{
    /**
     * nice when logging
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->format('Y-m-d');
    }
}
