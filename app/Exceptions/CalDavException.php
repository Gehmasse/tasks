<?php

namespace App\Exceptions;

use Exception;

class CalDavException extends Exception {
    public function __construct(string $response, string $ical)
    {
        parent::__construct($response . ' - ' . $ical);
    }
}
