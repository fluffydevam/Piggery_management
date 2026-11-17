<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('litters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farrowing_id')->unique()->constrained('farrowings')->cascadeOnDelete();
            $table->foreignId('cage_id')->nullable()->constrained()->nullOnDelete();
            $table->string('litter_tag', 50)->nullable();
            $table->enum('status', ['nursing', 'weaned', 'sold', 'finished', 'lost'])->default('nursing');
            $table->unsignedTinyInteger('current_count');
            $table->unsignedTinyInteger('deaths_count')->default(0);
            $table->date('weaned_date')->nullable();
            $table->unsignedTinyInteger('weaned_count')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('cage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('litters');
    }
};
