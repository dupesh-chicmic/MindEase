<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('moods')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasTable('quotes') && Schema::hasColumn('quotes', 'quote')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE quotes CHANGE quote text TEXT NOT NULL');
            } else {
                Schema::table('quotes', function (Blueprint $table) {
                    $table->renameColumn('quote', 'text');
                });
            }
        }

        if (Schema::hasColumn('moods', 'journal_written')) {
            Schema::table('moods', function (Blueprint $table) {
                $table->dropColumn('journal_written');
            });
        }

        if (Schema::hasColumn('moods', 'logged_date')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE moods CHANGE logged_date date DATE NOT NULL');
            } else {
                Schema::table('moods', function (Blueprint $table) {
                    $table->renameColumn('logged_date', 'date');
                });
            }
        }

        Schema::table('moods', function (Blueprint $table) {
            if (! Schema::hasColumn('moods', 'sleep_score')) {
                $table->unsignedTinyInteger('sleep_score')->nullable()->after('emoji');
            }
            if (! Schema::hasColumn('moods', 'stress_score')) {
                $table->unsignedTinyInteger('stress_score')->nullable()->after('sleep_score');
            }
            if (! Schema::hasColumn('moods', 'productivity_score')) {
                $table->unsignedTinyInteger('productivity_score')->nullable()->after('stress_score');
            }
            if (! Schema::hasColumn('moods', 'ate_well')) {
                $table->boolean('ate_well')->nullable()->after('productivity_score');
            }
        });

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE moods MODIFY mood_score TINYINT UNSIGNED NULL');
            DB::statement('ALTER TABLE moods MODIFY mood_label VARCHAR(255) NULL');
            DB::statement('ALTER TABLE moods MODIFY emoji VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        //
    }
};
