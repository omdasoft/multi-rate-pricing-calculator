<?php

namespace App\Domain\Pricing\Exceptions;

use DomainException;

/**
 * Raised by the calculation module when a line item's inputs are
 * numerically invalid. Mapped to a 422 JSON response by the exception
 * handler (see bootstrap/app.php).
 */
final class InvalidLineItemException extends DomainException
{
    private function __construct(string $message, private readonly string $field)
    {
        parent::__construct($message);
    }

    public static function discountExceedsSubtotal(int $discountCents, int $subtotalCents): self
    {
        return new self(
            "Fixed discount ({$discountCents} cents) cannot exceed the line subtotal ({$subtotalCents} cents).",
            'discount_fixed_cents',
        );
    }

    public function field(): string
    {
        return $this->field;
    }
}
