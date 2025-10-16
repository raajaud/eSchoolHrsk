<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('child_id');
            $table->string('occasion'); // e.g. "Good Habit", "Homework Done", etc.
            $table->integer('points'); // can be positive or negative
            $table->text('remarks')->nullable(); // optional note
            $table->date('date')->nullable(); // when the points were assigned
            $table->timestamps();

        });

        Schema::connection('school')->create('points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('child_id');
            $table->string('occasion'); // e.g. "Good Habit", "Homework Done", etc.
            $table->integer('points'); // can be positive or negative
            $table->text('remarks')->nullable(); // optional note
            $table->date('date')->nullable(); // when the points were assigned
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points');
        Schema::connection('school')->dropIfExists('points');
    }
};
