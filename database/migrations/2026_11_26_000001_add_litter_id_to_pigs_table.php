<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pigs', function (Blueprint $table): void {
            $table->foreignId('litter_id')
                ->nullable()
                ->after('cage_id')
                ->constrained('litters')
                ->nullOnDelete();

            $table->index('litter_id');
        });
    }

    public function down(): void
    {
        Schema::table('pigs', function (Blueprint $table): void {
            $table->dropIndex(['litter_id']);
            $table->dropConstrainedForeignId('litter_id');
        });
    }
};
