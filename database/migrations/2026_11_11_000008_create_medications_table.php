<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->enum('type', ['vaccine', 'vitamin', 'dewormer', 'antibiotic', 'treatment', 'supplement']);
            $table->string('default_dosage', 50)->nullable();
            $table->unsignedSmallInteger('withdrawal_days')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
