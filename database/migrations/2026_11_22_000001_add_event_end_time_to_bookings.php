<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Jam selesai acara (opsional), melengkapi jam mulai di formulir pemesanan. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $t) {
            $t->string('event_end_time', 5)->nullable()->after('event_time');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $t) {
            $t->dropColumn('event_end_time');
        });
    }
};
