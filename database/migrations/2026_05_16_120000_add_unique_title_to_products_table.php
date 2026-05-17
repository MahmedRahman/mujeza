<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeProductTitles();
        $this->deduplicateProductTitles();

        Schema::table('products', function (Blueprint $table) {
            $table->unique('title');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['title']);
        });
    }

    private function normalizeProductTitles(): void
    {
        DB::table('products')
            ->orderBy('id')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $normalized = trim(preg_replace('/\s+/u', ' ', (string) $product->title) ?? (string) $product->title);
                    if ($normalized !== '' && $normalized !== $product->title) {
                        DB::table('products')->where('id', $product->id)->update(['title' => $normalized]);
                    }
                }
            });
    }

    private function deduplicateProductTitles(): void
    {
        $duplicateTitles = DB::table('products')
            ->select('title')
            ->groupBy('title')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('title');

        foreach ($duplicateTitles as $title) {
            $rows = DB::table('products')
                ->where('title', $title)
                ->orderBy('id')
                ->get(['id', 'title']);

            foreach ($rows->skip(1) as $row) {
                $newTitle = $title.' #'.$row->id;
                DB::table('products')->where('id', $row->id)->update(['title' => $newTitle]);
            }
        }

        // إعادة الفحص إذا بقي تكرار بعد التسمية (نادر)
        $stillDuplicate = DB::table('products')
            ->select('title')
            ->groupBy('title')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($stillDuplicate) {
            DB::table('products')
                ->orderBy('id')
                ->chunkById(100, function ($products) {
                    $seen = [];
                    foreach ($products as $product) {
                        $title = (string) $product->title;
                        if (! isset($seen[$title])) {
                            $seen[$title] = true;
                            continue;
                        }
                        DB::table('products')
                            ->where('id', $product->id)
                            ->update(['title' => $title.' #'.$product->id]);
                    }
                });
        }
    }
};
