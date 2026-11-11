<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pigs', function (Blueprint $table) {
            $table->id();
            $table->string('tag_number', 50)->unique();
            $table->enum('type', ['sow', 'boar', 'gilt', 'piglet']);
            $table->string('breed', 100)->nullable();
            $table->enum('sex', ['female', 'male']);
            $table->date('birth_date')->nullable();
            $table->foreignId('cage_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'sold', 'culled', 'deceased'])->default('active');
            $table->string('origin')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'status']);
            $table->index('cage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigs');
    }
};
