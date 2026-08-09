<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Policies\DocumentPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(DocumentPolicy::class)]
class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'customer', 
        'issue_date', 
        'status',
        'subtotal_cents', 
        'total_discount_cents', 
        'total_tax_cents', 
        'grand_total_cents',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'status' => DocumentStatus::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(LineItem::class)->orderBy('position');
    }

    public function isDraft(): bool
    {
        return $this->status === DocumentStatus::Draft;
    }
}
