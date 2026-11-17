<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every farm record belongs to exactly one account. `medications.user_id`
     * is the one exception: a NULL owner marks a built-in catalog entry that
     * is shared by every account.
     *
     * @var list<string>
     */
    private array $tables = [
        'cages',
        'pigs',
        'breedings',
        'farrowings',
        'litters',
        'feeds',
        'feed_transactions',
        'medications',
        'medical_logs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->foreignId('user_id')
                        ->nullable()
                        ->after('id')
                        ->constrained()
                        ->cascadeOnDelete();
                });
            }

            if (! Schema::hasIndex($table, "{$table}_user_id_index")) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index('user_id'));
            }
        }

        // Hand any pre-existing single-farm data to the oldest account, so the
        // original admin keeps seeing it after the upgrade.
        $ownerId = DB::table('users')->orderBy('id')->value('id');

        if ($ownerId !== null) {
            foreach ($this->tables as $table) {
                DB::table($table)->whereNull('user_id')->update(['user_id' => $ownerId]);
            }
        }

        // Identifiers are only unique inside one account: two farms may both
        // own a pig tagged "PIG-001" or a cage named "G1".
        Schema::table('cages', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('cages_cage_number_unique');
            $blueprint->unique(['user_id', 'cage_number'], 'cages_user_id_cage_number_unique');
        });

        Schema::table('pigs', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('pigs_tag_number_unique');
            $blueprint->unique(['user_id', 'tag_number'], 'pigs_user_id_tag_number_unique');
        });

        Schema::table('feeds', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('feeds_name_unique');
            $blueprint->unique(['user_id', 'name'], 'feeds_user_id_name_unique');
        });

        Schema::table('medications', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('medications_name_unique');
            $blueprint->unique(['user_id', 'name'], 'medications_user_id_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('medications', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('medications_user_id_name_unique');
            $blueprint->unique('name');
        });

        Schema::table('feeds', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('feeds_user_id_name_unique');
            $blueprint->unique('name');
        });

        Schema::table('pigs', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('pigs_user_id_tag_number_unique');
            $blueprint->unique('tag_number');
        });

        Schema::table('cages', function (Blueprint $blueprint): void {
            $blueprint->dropUnique('cages_user_id_cage_number_unique');
            $blueprint->unique('cage_number');
        });

        foreach (['cages', 'pigs', 'breedings', 'farrowings', 'litters', 'feeds', 'medical_logs', 'medications'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropConstrainedForeignId('user_id');
            });
        }
    }
};
