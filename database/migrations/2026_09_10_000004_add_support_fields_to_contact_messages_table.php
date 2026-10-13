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
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->text('reply')->nullable()->after('message');
            $table->foreignId('replied_by')->nullable()->after('reply')->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable()->after('replied_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['replied_by']);
            $table->dropColumn(['user_id', 'reply', 'replied_by', 'replied_at']);
        });
    }
};
