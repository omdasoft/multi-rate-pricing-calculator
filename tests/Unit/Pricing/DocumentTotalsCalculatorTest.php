<?php

namespace Tests\Unit\Pricing;

use App\Domain\Pricing\DocumentTotalsCalculator;
use App\Domain\Pricing\DTOs\LineItemCalculation;
use PHPUnit\Framework\TestCase;

class DocumentTotalsCalculatorTest extends TestCase
{
    public function test_totals_match_the_three_line_sample_document(): void
    {
        $calculator = new DocumentTotalsCalculator();

        $widgetA = new LineItemCalculation(20000, 2000, 18000, 900, 18900);
        $widgetB = new LineItemCalculation(5000, 0, 5000, 250, 5250);
        $serviceFee = new LineItemCalculation(20000, 2000, 18000, 0, 18000);

        $totals = $calculator->calculate([$widgetA, $widgetB, $serviceFee]);

        $this->assertSame(45000, $totals->subtotalCents);       // $450.00
        $this->assertSame(4000, $totals->totalDiscountCents);   // $40.00
        $this->assertSame(1150, $totals->totalTaxCents);        // $11.50
        $this->assertSame(42150, $totals->grandTotalCents);     // $421.50
    }

    public function test_empty_line_list_produces_zero_totals(): void
    {
        $totals = (new DocumentTotalsCalculator())->calculate([]);

        $this->assertSame(0, $totals->subtotalCents);
        $this->assertSame(0, $totals->grandTotalCents);
    }
}
