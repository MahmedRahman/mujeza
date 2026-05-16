<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('changed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('orders')->orderBy('id')->chunkById(100, function ($orders) use ($now) {
            $rows = [];
            foreach ($orders as $order) {
                $rows[] = [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'changed_by' => null,
                    'note' => 'الحالة الأولية',
                    'created_at' => $order->created_at ?? $now,
                    'updated_at' => $order->created_at ?? $now,
                ];
            }
            if ($rows !== []) {
                DB::table('order_status_histories')->insert($rows);
            }
        });

        DB::table('orders')
            ->whereNull('status_changed_at')
            ->update(['status_changed_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
