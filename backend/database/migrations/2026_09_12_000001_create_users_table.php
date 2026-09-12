<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Blueprint;
use Hypervel\Database\Migrations\Migration;
use Hypervel\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('logto_id')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->json('custom_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
