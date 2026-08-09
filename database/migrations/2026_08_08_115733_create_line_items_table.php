<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_cents');
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->unsignedInteger('discount_fixed_cents')->nullable();
            $table->decimal('tax_percent', 5, 2)->nullable();
            $table->unsignedInteger('subtotal_cents')->default(0);
            $table->unsignedInteger('discount_amount_cents')->default(0);
            $table->unsignedInteger('tax_amount_cents')->default(0);
            $table->unsignedInteger('line_total_cents')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
