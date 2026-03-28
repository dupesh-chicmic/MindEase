<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('mood', 64);
            $table->text('message');
            $table->string('language', 32)->default('hinglish');
            $table->timestamps();

            $table->index('mood');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
