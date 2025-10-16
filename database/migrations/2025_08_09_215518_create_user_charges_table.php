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
        Schema::connection('school')->create('user_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('charge_type'); // e.g. "Monthly Fee", "Admission Fee", "Books", "Uniform"
            $table->decimal('amount', 10, 2)->default(0.00); // Charge amount
            $table->text('description')->nullable(); // Optional note about the charge
            $table->date('charge_date'); // Date when charge is applied
            $table->boolean('is_paid')->default(false); // Whether charge is paid
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('school')->dropIfExists('user_charges');
    }
};
