<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('customer');
            $table->date('issue_date');
            $table->string('status')->default('draft'); // draft|finalized
            $table->unsignedInteger('subtotal_cents')->default(0);
            $table->unsignedInteger('total_discount_cents')->default(0);
            $table->unsignedInteger('total_tax_cents')->default(0);
            $table->unsignedInteger('grand_total_cents')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'issue_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
