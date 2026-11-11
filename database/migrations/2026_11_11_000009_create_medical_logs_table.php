<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_logs', function (Blueprint $table) {
            $table->id();
            $table->string('treatable_type');
            $table->unsignedBigInteger('treatable_id');
            $table->foreignId('medication_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['vaccine', 'vitamin', 'dewormer', 'treatment', 'other']);
            $table->date('date_administered');
            $table->string('dosage', 50)->nullable();
            $table->enum('route', ['im', 'iv', 'sc', 'oral', 'topical'])->nullable();
            $table->string('administered_by')->nullable();
            $table->date('next_due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['treatable_type', 'treatable_id']);
            $table->index('next_due_date');
            $table->index('date_administered');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_logs');
    }
};
