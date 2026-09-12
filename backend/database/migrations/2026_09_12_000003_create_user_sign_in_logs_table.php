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
        Schema::create('user_sign_in_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('device_info')->nullable();
            $table->timestamp('signed_in_at');
            $table->string('logto_event_id')->nullable();

            $table->index('user_id');
            $table->index('signed_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sign_in_logs');
    }
};
