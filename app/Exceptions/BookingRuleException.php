<?php

namespace App\Exceptions;

use RuntimeException;

/** Pelanggaran aturan bisnis. Pesannya aman ditampilkan ke pengguna. */
class BookingRuleException extends RuntimeException {}
