<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_fee', 12, 2)->default(0)->after('total_amount');
            $table->text('internal_notes')->nullable()->after('delivery_fee');
            $table->timestamp('status_changed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee', 'internal_notes', 'status_changed_at']);
        });
    }
};
