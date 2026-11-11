<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained('feeds')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity_kg', 12, 2);
            $table->date('transaction_date');
            $table->string('reference')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['feed_id', 'transaction_date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_transactions');
    }
};
