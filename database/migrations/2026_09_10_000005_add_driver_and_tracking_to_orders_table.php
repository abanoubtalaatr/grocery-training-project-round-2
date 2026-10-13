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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('driver_id')->nullable()->after('address_id')->constrained('users')->nullOnDelete();
            $table->decimal('driver_latitude', 10, 8)->nullable()->after('notes');
            $table->decimal('driver_longitude', 11, 8)->nullable()->after('driver_latitude');
            $table->timestamp('driver_location_updated_at')->nullable()->after('driver_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn([
                'driver_id',
                'driver_latitude',
                'driver_longitude',
                'driver_location_updated_at',
            ]);
        });
    }
};
