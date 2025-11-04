<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('school')->create('daily_words', function (Blueprint $table) {
            $table->id();
            $table->string('english_word');
            $table->string('pronunciation')->nullable();
            $table->string('hindi_word');
            $table->string('hindi_meaning')->nullable();
            $table->date('publish_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('school')->dropIfExists('daily_words');
    }
};
