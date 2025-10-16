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
        Schema::connection('school')->create('points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('child_id');
            $table->string('occasion'); // e.g. "Good Habit", "Homework Done", etc.
            $table->integer('points'); // can be positive or negative
            $table->text('remarks')->nullable(); // optional note
            $table->date('date')->nullable(); // when the points were assigned
            $table->timestamps();
        });

        DB::connection('school')->table('points')->insert([
            ['id' => 1, 'child_id' => 392, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:37:59', 'updated_at' => '2025-05-27 10:37:59'],
            ['id' => 2, 'child_id' => 354, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:38:12', 'updated_at' => '2025-05-27 10:38:12'],
            ['id' => 3, 'child_id' => 351, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:38:24', 'updated_at' => '2025-05-27 10:38:24'],
            ['id' => 4, 'child_id' => 325, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:38:42', 'updated_at' => '2025-05-27 10:38:42'],
            ['id' => 5, 'child_id' => 317, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:39:02', 'updated_at' => '2025-05-27 10:39:02'],
            ['id' => 6, 'child_id' => 379, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:39:34', 'updated_at' => '2025-05-27 10:39:34'],
            ['id' => 7, 'child_id' => 375, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:39:49', 'updated_at' => '2025-05-27 10:39:49'],
            ['id' => 8, 'child_id' => 373, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:39:49', 'updated_at' => '2025-05-27 10:39:49'],
            ['id' => 9, 'child_id' => 353, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:09', 'updated_at' => '2025-05-27 10:40:09'],
            ['id' => 10, 'child_id' => 350, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:09', 'updated_at' => '2025-05-27 10:40:09'],
            ['id' => 11, 'child_id' => 342, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:09', 'updated_at' => '2025-05-27 10:40:09'],
            ['id' => 12, 'child_id' => 340, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:27', 'updated_at' => '2025-05-27 10:40:27'],
            ['id' => 13, 'child_id' => 338, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:27', 'updated_at' => '2025-05-27 10:40:27'],
            ['id' => 14, 'child_id' => 327, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:27', 'updated_at' => '2025-05-27 10:40:27'],
            ['id' => 15, 'child_id' => 317, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:44', 'updated_at' => '2025-05-27 10:40:44'],
            ['id' => 16, 'child_id' => 316, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:44', 'updated_at' => '2025-05-27 10:40:44'],
            ['id' => 17, 'child_id' => 314, 'occasion' => 'Food', 'points' => 15, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:44', 'updated_at' => '2025-05-27 10:40:44'],
            ['id' => 18, 'child_id' => 305, 'occasion' => 'Food', 'points' => 10, 'remarks' => 'Good Habits', 'date' => '2025-05-27', 'created_at' => '2025-05-27 10:40:57', 'updated_at' => '2025-05-27 10:40:57'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('school')->dropIfExists('points');
    }
};


// TRUNCATE `extra_user_datas`;
// TRUNCATE `user_charges`;
// TRUNCATE `points`;
// TRUNCATE `students`;
// SET FOREIGN_KEY_CHECKS = 0;
// TRUNCATE `payment_transactions`;
// TRUNCATE TABLE users;
// SET FOREIGN_KEY_CHECKS = 1;
// INSERT INTO `users` (`id`, `first_name`, `last_name`, `mobile`, `email`, `password`, `gender`, `image`, `dob`, `current_address`, `permanent_address`, `occupation`, `status`, `reset_request`, `fcm_id`, `school_id`, `language`, `remember_token`, `email_verified_at`, `two_factor_enabled`, `two_factor_secret`, `two_factor_expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (NULL, 'Asif', 'Admin', '9239192393', 'hrskschool@gmail.com', '$2y$10$AIojEWCeb9m5JKnSSEg3k.vMBYbufVLmdxjkOCvCB8xriIAtQcn9G', NULL, '', NULL, NULL, NULL, NULL, '1', '0', NULL, '5', 'en', NULL, '2025-05-25 12:41:45', '0', NULL, NULL, '2025-05-25 08:33:26', '2025-06-11 11:15:07', NULL);
//  UPDATE users
// SET id = 6
// WHERE email = 'hrskschool@gmail.com';
// ALTER TABLE users AUTO_INCREMENT = 300;

