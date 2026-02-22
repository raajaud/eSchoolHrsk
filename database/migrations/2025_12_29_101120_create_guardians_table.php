<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('school')->create('guardians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('mobile', 15);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('school')->dropIfExists('guardians');
    }
};

// CREATE TABLE `guardians` (
//   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
//   `user_id` BIGINT UNSIGNED NOT NULL,
//   `name` VARCHAR(255) NOT NULL,
//   `mobile` VARCHAR(15) NOT NULL,
//   `created_at` TIMESTAMP NULL DEFAULT NULL,
//   `updated_at` TIMESTAMP NULL DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
