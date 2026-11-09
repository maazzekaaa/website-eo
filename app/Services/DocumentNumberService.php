<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /** Nomor berikutnya, berurutan per bulan: INV/2026/11/012. */
    public function next(string $prefix, ?CarbonInterface $at = null): string
    {
        $at ??= now();
        $period = $at->format('Y-m');

        return DB::transaction(function () use ($prefix, $period, $at) {
            DB::table('document_sequences')->insertOrIgnore(['prefix' => $prefix, 'period' => $period, 'last_number' => 0]);
            $sequence = DB::table('document_sequences')->where(['prefix' => $prefix, 'period' => $period])->lockForUpdate()->first();
            $number = $sequence->last_number + 1;
            DB::table('document_sequences')->where('id', $sequence->id)->update(['last_number' => $number]);

            return sprintf('%s/%s/%03d', $prefix, $at->format('Y/m'), $number);
        }, 3);
    }
}
