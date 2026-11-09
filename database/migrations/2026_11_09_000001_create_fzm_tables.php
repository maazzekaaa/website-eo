<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('company_name');
            $t->string('address')->nullable();
            $t->string('email')->nullable();
            $t->string('wa_number')->nullable();
            $t->string('npwp')->nullable();
            $t->string('bank_name')->nullable();
            $t->string('bank_account')->nullable();
            $t->string('bank_holder')->nullable();
            $t->string('signature_path')->nullable();
            $t->boolean('tax_enabled')->default(false);
            $t->unsignedTinyInteger('tax_rate')->default(11);
            $t->string('hero_title')->nullable();
            $t->text('hero_subtitle')->nullable();
            $t->string('hero_image')->nullable();
            $t->text('about')->nullable();
            $t->string('opening_hours')->nullable();
            $t->string('map_embed', 1000)->nullable();
            $t->string('instagram_url')->nullable();
            $t->string('tiktok_url')->nullable();
            $t->string('youtube_url')->nullable();
            $t->timestamps();
        });

        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('category')->nullable();
            $t->enum('pricing_type', ['tier', 'per_unit']);
            $t->string('unit_label', 20)->nullable();
            $t->unsignedBigInteger('unit_price')->nullable();
            $t->enum('badge', ['promo', 'baru'])->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('service_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unsignedBigInteger('price');
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('logo')->nullable();
            $t->text('description')->nullable();
            $t->string('website')->nullable();
            $t->timestamps();
        });

        Schema::create('portfolios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->enum('category', ['Wedding', 'Corporate', 'Concert', 'Photoshoot']);
            $t->date('event_date')->nullable();
            $t->string('location')->nullable();
            $t->string('caption')->nullable();
            $t->string('services')->nullable();
            $t->text('story')->nullable();
            $t->string('cover_image')->nullable();
            $t->enum('folder_color', ['red', 'black', 'maroon', 'silver'])->default('red');
            $t->boolean('is_featured')->default(false);
            $t->timestamps();
        });

        Schema::create('portfolio_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->string('caption')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->string('booking_code', 20)->unique();
            $t->string('invoice_number', 30)->unique();
            $t->string('customer_name');
            $t->string('customer_wa', 20);
            $t->date('event_date')->index();
            $t->string('event_time', 5)->nullable();
            $t->string('event_location');
            $t->text('note')->nullable();
            $t->unsignedBigInteger('subtotal');
            $t->unsignedTinyInteger('tax_rate')->default(0);
            $t->unsignedBigInteger('tax_amount')->default(0);
            $t->unsignedBigInteger('total');
            $t->unsignedBigInteger('dp_amount');
            $t->enum('status', ['pending', 'confirmed', 'completed', 'rejected', 'cancelled'])->default('pending')->index();
            $t->enum('payment_status', ['unpaid', 'dp', 'paid'])->default('unpaid')->index();
            // Pengaman terakhir anti double booking: satu tanggal, satu booking yang mengunci.
            $t->date('confirmed_date')->nullable()->unique()
                ->storedAs("IF(status IN ('confirmed','completed'), event_date, NULL)");
            $t->timestamp('contacted_at')->nullable();
            $t->text('admin_note')->nullable();
            $t->timestamps();
        });

        Schema::create('booking_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('booking_id')->constrained()->restrictOnDelete();
            $t->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('service_option_id')->nullable()->constrained()->nullOnDelete();
            $t->string('description');
            $t->unsignedInteger('qty');
            $t->string('unit', 20);
            $t->unsignedBigInteger('unit_price');
            $t->unsignedBigInteger('subtotal');
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('booking_id')->constrained()->restrictOnDelete();
            $t->string('receipt_number', 30)->unique();
            $t->unsignedBigInteger('amount');
            $t->enum('type', ['dp', 'pelunasan', 'lainnya']);
            $t->enum('method', ['transfer', 'tunai']);
            $t->date('paid_at')->index();
            $t->string('proof_path')->nullable();
            $t->text('note')->nullable();
            $t->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $t->timestamp('voided_at')->nullable();
            $t->string('void_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('document_sequences', function (Blueprint $t) {
            $t->id();
            $t->string('prefix', 10);
            $t->string('period', 7);
            $t->unsignedInteger('last_number')->default(0);
            $t->unique(['prefix', 'period']);
        });
    }

    public function down(): void
    {
        foreach (['document_sequences', 'payments', 'booking_items', 'bookings', 'portfolio_images', 'portfolios', 'clients', 'service_options', 'services', 'settings', 'admins'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
