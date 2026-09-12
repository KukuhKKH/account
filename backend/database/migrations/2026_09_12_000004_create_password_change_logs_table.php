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
        Schema::create('password_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('change_type')->default('self_change');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('via_logto_api')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('changed_by_user_id');
            $table->index('change_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_change_logs');
    }
};
