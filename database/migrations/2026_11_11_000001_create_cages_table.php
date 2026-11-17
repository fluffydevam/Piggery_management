<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cages', function (Blueprint $table) {
            $table->id();
            $table->string('cage_number', 50)->unique();
            $table->enum('type', ['gestation', 'farrowing', 'boar', 'weaning', 'grower', 'quarantine'])->default('gestation');
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->string('location')->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance', 'inactive'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cages');
    }
};
