<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create new table with remote_jid as primary key
        Schema::create('customers_new', function (Blueprint $table) {
            $table->string('remote_jid')->primary();
            $table->string('phone')->nullable();
            $table->string('name');
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 2. Copy existing data — use remote_jid if set, otherwise fall back to phone
        DB::statement('
            INSERT INTO customers_new (remote_jid, phone, name, address, created_at, updated_at)
            SELECT
                COALESCE(NULLIF(remote_jid, ""), phone),
                phone,
                name,
                address,
                created_at,
                updated_at
            FROM customers
        ');

        // 3. Swap tables
        Schema::drop('customers');
        Schema::rename('customers_new', 'customers');
    }

    public function down(): void
    {
        Schema::create('customers_old', function (Blueprint $table) {
            $table->string('phone')->primary();
            $table->string('remote_jid')->nullable();
            $table->string('name');
            $table->text('address')->nullable();
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO customers_old (phone, remote_jid, name, address, created_at, updated_at)
            SELECT COALESCE(NULLIF(phone, ""), remote_jid), remote_jid, name, address, created_at, updated_at
            FROM customers
        ');

        Schema::drop('customers');
        Schema::rename('customers_old', 'customers');
    }
};
