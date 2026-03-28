<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mood_score')) {
                $table->integer('mood_score')->nullable()->after('age');
            }
            if (! Schema::hasColumn('users', 'mood_label')) {
                $table->string('mood_label', 50)->nullable()->after('mood_score');
            }
            if (! Schema::hasColumn('users', 'emoji')) {
                $table->string('emoji', 10)->nullable()->after('mood_label');
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['mood_score', 'mood_label', 'emoji'],
            fn (string $col) => Schema::hasColumn('users', $col)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
