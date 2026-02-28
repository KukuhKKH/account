<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('logto_id')->unique()->nullable()->after('id');
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone')->nullable()->after('avatar');
            $table->text('address')->nullable()->after('phone');
            $table->enum('role', ['superadmin', 'admin', 'user'])->default('user')->after('address')->index();
            $table->timestamp('last_login_at')->nullable()->after('role');
            $table->json('custom_data')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'logto_id',
                'avatar',
                'phone',
                'address',
                'role',
                'last_login_at',
                'custom_data',
            ]);
        });
    }
};
