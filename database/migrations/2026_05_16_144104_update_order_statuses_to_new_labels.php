<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $statusMap = [
        'قيد المعالجة' => 'طلب جديد',
        'تم الاستلام' => 'تم التأكيد',
        'قيد التجهيز' => 'قيد التجهيز',
        'تم الشحن' => 'خرج للتوصيل',
        'تم التسليم' => 'مكتمل',
        'ملغي' => 'ملغي',
    ];

    public function up(): void
    {
        foreach ($this->statusMap as $oldStatus => $newStatus) {
            DB::table('orders')
                ->where('status', $oldStatus)
                ->update(['status' => $newStatus]);
        }
    }

    public function down(): void
    {
        $reverseMap = array_flip($this->statusMap);

        foreach ($reverseMap as $newStatus => $oldStatus) {
            DB::table('orders')
                ->where('status', $newStatus)
                ->update(['status' => $oldStatus]);
        }
    }
};
