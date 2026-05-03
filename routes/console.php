<?php

use App\Models\Product;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('import:products {--csv=public/asset/products.csv} {--skip-existing} {--move-images} {--dry-run} {--limit=0}', function () {
    $csvRel = (string) $this->option('csv');
    $csvPath = base_path($csvRel);

    if (! is_file($csvPath)) {
        $this->error("CSV not found: {$csvPath}");
        return self::FAILURE;
    }

    $skipExisting = (bool) $this->option('skip-existing');
    $moveImages = (bool) $this->option('move-images');
    $dryRun = (bool) $this->option('dry-run');
    $limit = (int) $this->option('limit');

    $imagesSourceRootAbs = base_path('public/asset');
    $storedDir = 'products/gallery';
    $disk = Storage::disk('public');

    $parseMoney = function (?string $value): ?float {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        // Remove whitespace (some exports may add it around separators).
        $value = str_replace(["\t", ' '], '', $value);

        // Normalize Arabic punctuation.
        $value = str_replace(['٫', '٬'], ['.', ''], $value);

        $lastDot = strrpos($value, '.');
        $lastComma = strrpos($value, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                // "1,234.56" => remove commas.
                $value = str_replace(',', '', $value);
            } else {
                // "1.234,56" => remove dots and switch comma to dot.
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }
        } elseif ($lastComma !== false) {
            // "123,45" => comma decimal.
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // If there are multiple dots, keep only the last dot as decimal separator.
            if (substr_count($value, '.') > 1) {
                $value = preg_replace('/\.(?=.*\.)/', '', $value);
            }
        }

        // Keep digits, dot and minus only.
        $value = preg_replace('/[^0-9.\-]/', '', $value);
        if ($value === '' || $value === '.' || $value === '-') {
            return null;
        }

        return round((float) $value, 2);
    };

    $parseAvailability = function (?string $value): bool {
        $v = trim((string) $value);
        if ($v === '') {
            // Default to available when CSV field is empty.
            return true;
        }

        // Common Arabic values in the CSV.
        if (in_array($v, ['نعم', 'متاح', 'available', 'true', '1'], true)) {
            return true;
        }

        if (in_array($v, ['لا', 'غير متاح', 'false', '0'], true)) {
            return false;
        }

        // Fallback: try bool parsing.
        return filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    };

    $this->info("Starting import from: {$csvPath}");
    if ($dryRun) {
        $this->warn('Dry run enabled: DB/images will not be modified.');
    }
    if ($moveImages) {
        $this->warn('Move-images enabled: originals may be deleted after storing.');
    }

    $file = new \SplFileObject($csvPath);
    $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
    $file->setCsvControl(',', '"');

    $header = null;
    $colMap = [];

    $rowNo = 0;
    $counts = [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'image_missing' => 0,
        'stored' => 0,
        'failed' => 0,
    ];

    foreach ($file as $row) {
        $rowNo++;
        if (! is_array($row)) {
            continue;
        }

        // SplFileObject may return [null] on empty rows.
        if ($row === [null] || $row === null) {
            continue;
        }

        if ($header === null) {
            $header = array_map(function ($h) {
                $h = (string) $h;
                // Handle UTF-8 BOM sometimes present in exported CSV headers.
                $h = preg_replace('/^\xEF\xBB\xBF/', '', $h) ?? $h;
                return trim($h);
            }, $row);
            foreach ($header as $idx => $name) {
                if ($name !== '') {
                    $colMap[$name] = $idx;
                }
            }

            $required = ['اسم المنتج', 'الوصف', 'السعر قبل', 'السعر بعد', 'الصورة المحلية'];
            $missing = array_values(array_diff($required, array_keys($colMap)));
            if ($missing !== []) {
                $this->error('CSV header missing columns: ' . implode(', ', $missing));
                return self::FAILURE;
            }
            continue;
        }

        $title = trim((string) ($row[$colMap['اسم المنتج']] ?? ''));
        if ($title === '') {
            continue;
        }

        $description = (string) ($row[$colMap['الوصف']] ?? '');
        $priceBefore = $parseMoney($row[$colMap['السعر قبل']] ?? null);
        $priceAfter = $parseMoney($row[$colMap['السعر بعد']] ?? null);

        // DB requires "price" (non-null). If "السعر قبل" is empty, we treat "السعر بعد" as base.
        $price = $priceBefore ?? $priceAfter;
        $discountPrice = $priceBefore === null ? null : $priceAfter;

        $isAvailable = true;
        if (isset($colMap['متاح'])) {
            $isAvailable = $parseAvailability($row[$colMap['متاح']] ?? null);
        }

        $localImageRel = trim((string) ($row[$colMap['الصورة المحلية']] ?? ''));
        $localImageAbs = $localImageRel !== '' ? $imagesSourceRootAbs . DIRECTORY_SEPARATOR . $localImageRel : '';
        if ($localImageRel !== '' && ! is_file($localImageAbs)) {
            // Fallback: if CSV provides only "product_x.webp" without "images/".
            $localImageAbs = $imagesSourceRootAbs . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . basename($localImageRel);
        }

        $existing = Product::query()->where('title', $title)->first();
        if ($skipExisting && $existing) {
            $counts['skipped']++;
            continue;
        }

        if ($price === null) {
            $counts['failed']++;
            $this->error("Row #{$rowNo}: Missing price for '{$title}'");
            continue;
        }

        $galleryStoredPath = null;
        if ($localImageRel !== '' && is_file($localImageAbs)) {
            $basename = basename($localImageAbs);
            $galleryStoredPath = $storedDir . '/' . $basename;

            if (! $dryRun) {
                $bytes = file_get_contents($localImageAbs);
                if ($bytes === false) {
                    $this->error("Row #{$rowNo}: Failed to read image bytes: {$localImageAbs}");
                    $counts['failed']++;
                    continue;
                }

                $disk->put($galleryStoredPath, $bytes);

                if ($moveImages) {
                    @unlink($localImageAbs);
                }
            }

            $counts['stored']++;
        } else {
            $counts['image_missing']++;
        }

        $payload = [
            'title' => $title,
            'price' => $price,
            'discount_price' => $discountPrice,
            'description' => $description,
            'is_available' => $isAvailable,
        ];

        // Only overwrite product images if we actually stored a new gallery image.
        // This prevents clearing cover/gallery when the source image is missing.
        if ($galleryStoredPath !== null) {
            $payload['cover_image'] = $galleryStoredPath;
            $payload['gallery_images'] = [$galleryStoredPath];
            $payload['primary_gallery_image'] = $galleryStoredPath;
        }

        try {
            if ($existing) {
                if (! $dryRun) {
                    $existing->update($payload);
                }
                $counts['updated']++;
            } else {
                if (! $dryRun) {
                    Product::query()->create($payload);
                }
                $counts['created']++;
            }
        } catch (\Throwable $e) {
            $counts['failed']++;
            $this->error("Row #{$rowNo}: {$e->getMessage()}");
            continue;
        }

        if ($limit > 0 && ($counts['created'] + $counts['updated']) >= $limit) {
            break;
        }
    }

    $this->info('Import finished.');
    $this->line('Created: ' . $counts['created']);
    $this->line('Updated: ' . $counts['updated']);
    $this->line('Skipped: ' . $counts['skipped']);
    $this->line('Images stored: ' . $counts['stored']);
    $this->line('Images missing: ' . $counts['image_missing']);
    $this->line('Failed: ' . $counts['failed']);

    return self::SUCCESS;
})->purpose('Import products from CSV and link product images');
