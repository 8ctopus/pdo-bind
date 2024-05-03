<?php

// php does not distinguish between a date and a date time hence this class

declare(strict_types=1);

namespace Oct8pus\PDOWrap;

use DateTime;

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
