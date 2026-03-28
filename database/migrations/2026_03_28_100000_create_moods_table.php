<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('mood_score')->nullable();
            $table->string('mood_label')->nullable();
            $table->string('emoji')->nullable();
            $table->unsignedTinyInteger('sleep_score')->nullable();
            $table->unsignedTinyInteger('stress_score')->nullable();
            $table->unsignedTinyInteger('productivity_score')->nullable();
            $table->boolean('ate_well')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moods');
    }
};
