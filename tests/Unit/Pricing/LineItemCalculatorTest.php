<?php

namespace Tests\Unit\Pricing;

use App\Domain\Pricing\DTOs\LineItemInput;
use App\Domain\Pricing\Exceptions\InvalidLineItemException;
use App\Domain\Pricing\LineItemCalculator;
use PHPUnit\Framework\TestCase;

class LineItemCalculatorTest extends TestCase
{
    private LineItemCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new LineItemCalculator();
    }

    public function test_percent_discount_then_tax_matches_widget_a_sample(): void
    {
        $result = $this->calculator->calculate(new LineItemInput(
            quantity: 2,
            unitPriceCents: 10000, // $100.00
            discountPercent: 10.0,
            taxPercent: 5.0,
        ));

        $this->assertSame(20000, $result->subtotalCents);       // $200.00
        $this->assertSame(2000, $result->discountAmountCents);  // $20.00
        $this->assertSame(18000, $result->afterDiscountCents);  // $180.00
        $this->assertSame(900, $result->taxAmountCents);        // $9.00 (5% of 180, not 200)
        $this->assertSame(18900, $result->lineTotalCents);      // $189.00
    }

    public function test_no_discount_with_tax_matches_widget_b_sample(): void
    {
        $result = $this->calculator->calculate(new LineItemInput(
            quantity: 1,
            unitPriceCents: 5000, // $50.00
            taxPercent: 5.0,
        ));

        $this->assertSame(5000, $result->subtotalCents);
        $this->assertSame(0, $result->discountAmountCents);
        $this->assertSame(250, $result->taxAmountCents);   // $2.50
        $this->assertSame(5250, $result->lineTotalCents);  // $52.50
    }

    public function test_fixed_discount_with_no_tax_matches_service_fee_sample(): void
    {
        $result = $this->calculator->calculate(new LineItemInput(
            quantity: 1,
            unitPriceCents: 20000, // $200.00
            discountFixedCents: 2000, // $20.00
        ));

        $this->assertSame(20000, $result->subtotalCents);
        $this->assertSame(2000, $result->discountAmountCents);
        $this->assertSame(18000, $result->afterDiscountCents);
        $this->assertSame(0, $result->taxAmountCents);
        $this->assertSame(18000, $result->lineTotalCents);
    }

    public function test_fixed_discount_exceeding_subtotal_is_rejected(): void
    {
        $this->expectException(InvalidLineItemException::class);

        $this->calculator->calculate(new LineItemInput(
            quantity: 1,
            unitPriceCents: 1000, // $10.00
            discountFixedCents: 5000, // $50.00 — exceeds subtotal
        ));
    }

    public function test_fixed_discount_exactly_equal_to_subtotal_is_allowed(): void
    {
        $result = $this->calculator->calculate(new LineItemInput(
            quantity: 1,
            unitPriceCents: 1000,
            discountFixedCents: 1000,
        ));

        $this->assertSame(0, $result->afterDiscountCents);
        $this->assertSame(0, $result->lineTotalCents);
    }

    public function test_tax_rounds_half_up_to_the_nearest_cent(): void
    {
        // 33 cents after discount, 12.5% tax => 4.125 cents, rounds to 4.
        $result = $this->calculator->calculate(new LineItemInput(
            quantity: 1,
            unitPriceCents: 33,
            taxPercent: 12.5,
        ));

        $this->assertSame(4, $result->taxAmountCents);
        $this->assertSame(37, $result->lineTotalCents);
    }

    public function test_zero_quantity_produces_zero_subtotal_without_error(): void
    {
        $result = $this->calculator->calculate(new LineItemInput(
            quantity: 0,
            unitPriceCents: 5000,
        ));

        $this->assertSame(0, $result->subtotalCents);
        $this->assertSame(0, $result->lineTotalCents);
    }
}
