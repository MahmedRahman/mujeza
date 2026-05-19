<?php

namespace Tests\Unit;

use App\Services\ProductSearchService;
use PHPUnit\Framework\TestCase;

class ProductSearchServiceTest extends TestCase
{
    private ProductSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProductSearchService;
    }

    public function test_partial_match_at_start_scores_high(): void
    {
        $score = $this->service->scoreTitle('عسل سدر', 'عسل السدر الجبلي عصار 500 جرام');

        $this->assertGreaterThan(400, $score);
    }

    public function test_partial_match_at_end_scores_high(): void
    {
        $score = $this->service->scoreTitle('500 جرام', 'عسل السدر الجبلي عصار 500 جرام');

        $this->assertGreaterThan(400, $score);
    }

    public function test_typo_still_matches_close_product_name(): void
    {
        $score = $this->service->scoreTitle('عسل سضر', 'عسل السدر الجبلي عصار 500 جرام');
        $exact = $this->service->scoreTitle('شامبو', 'عسل السدر الجبلي عصار 500 جرام');

        $this->assertGreaterThan($exact, $score);
    }

    public function test_short_fragment_matches_product_keyword(): void
    {
        $score = $this->service->scoreTitle('مانوكا', 'عسل المانوكا 500جم (10+ Active)');

        $this->assertGreaterThan(350, $score);
    }
}
