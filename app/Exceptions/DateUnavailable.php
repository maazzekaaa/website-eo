<?php

namespace App\Exceptions;

class DateUnavailable extends BookingRuleException
{
    public function __construct(string $message = 'Tanggal ini sudah terisi. Silakan pilih tanggal lain.')
    {
        parent::__construct($message);
    }
}
