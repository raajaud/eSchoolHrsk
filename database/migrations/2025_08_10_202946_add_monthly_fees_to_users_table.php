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
        Schema::connection('school')->table('users', function (Blueprint $table) {
            $table->decimal('monthly_fees', 10, 2)
                  ->default(0.00)
                  ->after('email'); // place it after email (change if needed)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('school')->table('users', function (Blueprint $table) {
            $table->dropColumn('monthly_fees');
        });
    }
};
