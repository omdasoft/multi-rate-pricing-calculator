<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'description', 
        'quantity', 
        'unit_price_cents',
        'discount_percent', 
        'discount_fixed_cents', 
        'tax_percent',
        'subtotal_cents', 
        'discount_amount_cents', 
        'tax_amount_cents', 
        'line_total_cents',
        'position',
    ];

    protected $casts = [
        'discount_percent' => 'float',
        'tax_percent' => 'float',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
