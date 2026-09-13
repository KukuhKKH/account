<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Blueprint;
use Hypervel\Database\Migrations\Migration;
use Hypervel\Support\Facades\DB;
use Hypervel\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 32)->default('active')->after('address');
            $table->softDeletes()->after('updated_at');

            $table->index('status');
        });

        // Migrate legacy custom_data['is_suspended'] to the new status column
        $users = DB::table('users')->whereNotNull('custom_data')->get();

        foreach ($users as $user) {
            $customData = json_decode((string) $user->custom_data, true);

            if (is_array($customData) && array_key_exists('is_suspended', $customData)) {
                $isSuspended = (bool) $customData['is_suspended'];
                $newStatus   = $isSuspended ? 'suspended' : 'active';

                unset($customData['is_suspended']);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'status'      => $newStatus,
                        'custom_data' => empty($customData) ? null : json_encode($customData),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
            $table->dropSoftDeletes();
        });
    }
};
