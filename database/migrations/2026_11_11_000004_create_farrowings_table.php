<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('breeding_id')->unique()->constrained('breedings')->cascadeOnDelete();
            $table->date('farrowing_date');
            $table->unsignedTinyInteger('born_alive')->default(0);
            $table->unsignedTinyInteger('stillborn')->default(0);
            $table->unsignedTinyInteger('mummified')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('farrowing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farrowings');
    }
};
