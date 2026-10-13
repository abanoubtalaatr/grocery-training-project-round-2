<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop database-level unique constraints on username, email, phone.
     *
     * The Laravel validation rules already enforce uniqueness while ignoring
     * soft-deleted rows (unique:users,column,NULL,id,deleted_at,NULL).
     * Database-level unique constraints prevent re-registration after soft
     * delete because the old row still exists with the same values.
     *
     * Application-level validation is the primary guard; this migration
     * removes the conflicting database-level constraint.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['email']);
            $table->dropUnique(['phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
            $table->unique('email');
            $table->unique('phone');
        });
    }
};
