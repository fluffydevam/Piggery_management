<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breedings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sow_id')->constrained('pigs')->restrictOnDelete();
            $table->foreignId('boar_id')->nullable()->constrained('pigs')->nullOnDelete();
            $table->date('breeding_date');
            $table->enum('method', ['natural', 'ai'])->default('natural');
            $table->string('semen_source')->nullable();
            $table->date('expected_farrowing_date');
            $table->enum('pregnancy_status', ['pending', 'confirmed', 'not_pregnant', 'aborted', 'farrowed'])->default('pending');
            $table->date('confirmed_pregnant_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('expected_farrowing_date');
            $table->index(['pregnancy_status', 'expected_farrowing_date']);
            $table->index('sow_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breedings');
    }
};
