<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->enum('category', ['starter', 'grower', 'finisher', 'gestating', 'lactating', 'boar', 'supplement']);
            $table->decimal('bag_weight_kg', 8, 2)->default(50);
            $table->decimal('stock_kg', 12, 2)->default(0);
            $table->decimal('reorder_level_kg', 12, 2)->default(100);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
