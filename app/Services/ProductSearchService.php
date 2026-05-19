<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductSearchService
{
    private const MIN_SCORE = 35.0;

    private const DEFAULT_LIMIT = 20;

    private const MAX_LIMIT = 50;

    /**
     * @return Collection<int, array{product: Product, score: float}>
     */
    public function search(string $query, int $limit = self::DEFAULT_LIMIT): Collection
    {
        $query = $this->normalize($query);

        if ($query === '') {
            return collect();
        }

        $limit = max(1, min($limit, self::MAX_LIMIT));

        return Product::query()
            ->orderBy('title')
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'score' => $this->scoreTitle($query, (string) $product->title),
            ])
            ->filter(fn (array $row) => $row['score'] >= self::MIN_SCORE)
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    public function scoreTitle(string $query, string $title): float
    {
        $query = $this->normalize($query);
        $title = $this->normalize($title);

        if ($query === '' || $title === '') {
            return 0.0;
        }

        if ($query === $title) {
            return 1000.0;
        }

        $score = 0.0;

        if (str_contains($title, $query)) {
            $score += 520.0;

            if (str_starts_with($title, $query)) {
                $score += 160.0;
            }

            if (str_ends_with($title, $query)) {
                $score += 120.0;
            }

            $position = mb_strpos($title, $query);
            if ($position !== false) {
                $score += max(0.0, 60.0 - ($position * 2));
            }
        }

        $queryWords = $this->words($query);
        $titleWords = $this->words($title);

        if ($queryWords !== []) {
            $wordScore = 0.0;

            foreach ($queryWords as $word) {
                if (mb_strlen($word) < 2) {
                    continue;
                }

                $wordScore += $this->bestWordScore($word, $title, $titleWords);
            }

            $score += ($wordScore / count($queryWords)) * 320.0;
        }

        similar_text($query, $title, $wholeSimilarity);
        $score += $wholeSimilarity * 2.2;

        $score += $this->bestSubstringSimilarity($query, $title) * 1.6;

        return round($score, 2);
    }

    private function bestWordScore(string $word, string $title, array $titleWords): float
    {
        if (str_contains($title, $word)) {
            return 1.0;
        }

        $best = 0.0;

        foreach ($titleWords as $titleWord) {
            if (mb_strlen($titleWord) < 2) {
                continue;
            }

            similar_text($word, $titleWord, $similarity);
            $best = max($best, $similarity / 100);

            $distance = $this->mbLevenshtein($word, $titleWord);
            $maxLength = max(mb_strlen($word), mb_strlen($titleWord));
            $best = max($best, 1 - ($distance / max($maxLength, 1)));
        }

        return $best;
    }

    private function bestSubstringSimilarity(string $query, string $title): float
    {
        $best = 0.0;
        $queryLength = mb_strlen($query);

        foreach ($this->words($title) as $titleWord) {
            if (mb_strlen($titleWord) < 2) {
                continue;
            }

            similar_text($query, $titleWord, $similarity);
            $best = max($best, $similarity);

            $distance = $this->mbLevenshtein($query, $titleWord);
            $maxLength = max($queryLength, mb_strlen($titleWord));
            $best = max($best, (1 - ($distance / max($maxLength, 1))) * 100);
        }

        $titleLength = mb_strlen($title);

        for ($start = 0; $start < $titleLength; $start++) {
            $maxWindow = min($queryLength + 3, $titleLength - $start);

            for ($length = max(2, $queryLength - 2); $length <= $maxWindow; $length++) {
                $chunk = mb_substr($title, $start, $length);

                similar_text($query, $chunk, $similarity);
                $best = max($best, $similarity);

                $distance = $this->mbLevenshtein($query, $chunk);
                $maxLength = max($queryLength, mb_strlen($chunk));
                $best = max($best, (1 - ($distance / max($maxLength, 1))) * 100);
            }
        }

        return $best;
    }

    /**
     * @return list<string>
     */
    private function words(string $text): array
    {
        $parts = preg_split('/[\s\-\/\(\)\[\],،]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($parts) ? array_values(array_filter($parts, fn (string $word) => $word !== '')) : [];
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        if ($text === '') {
            return '';
        }

        $text = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06DC}\x{06DF}-\x{06E4}\x{06E7}\x{06E8}\x{06EA}-\x{06ED}]/u', '', $text) ?? $text;
        $text = str_replace(['أ', 'إ', 'آ', 'ٱ', 'ﺃ', 'ﺇ', 'ﺁ'], 'ا', $text);
        $text = str_replace(['ى', 'ي', 'ئ', 'ﺉ', 'ﻯ', 'ﻳ'], 'ي', $text);
        $text = str_replace(['ة', 'ﺓ', 'ﺔ'], 'ه', $text);
        $text = str_replace(['ؤ', 'ﺅ'], 'و', $text);
        $text = preg_replace('/[^\p{L}\p{N}\s\+]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function mbLevenshtein(string $left, string $right): int
    {
        $leftLength = mb_strlen($left);
        $rightLength = mb_strlen($right);

        if ($leftLength === 0) {
            return $rightLength;
        }

        if ($rightLength === 0) {
            return $leftLength;
        }

        $previous = range(0, $rightLength);

        for ($i = 1; $i <= $leftLength; $i++) {
            $current = [$i];
            $leftChar = mb_substr($left, $i - 1, 1);

            for ($j = 1; $j <= $rightLength; $j++) {
                $cost = $leftChar === mb_substr($right, $j - 1, 1) ? 0 : 1;
                $current[$j] = min(
                    $previous[$j] + 1,
                    $current[$j - 1] + 1,
                    $previous[$j - 1] + $cost
                );
            }

            $previous = $current;
        }

        return $previous[$rightLength];
    }
}
