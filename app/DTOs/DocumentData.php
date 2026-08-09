<?php

namespace App\DTOs;

final readonly class DocumentData
{
    public function __construct(
        public ?string $title = null,
        public ?string $customer = null,
        public ?string $issueDate = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            customer: $data['customer'] ?? null,
            issueDate: $data['issue_date'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'customer' => $this->customer,
            'issueDate' => $this->issueDate
        ], fn($value) => !is_null($value));
    }
}
