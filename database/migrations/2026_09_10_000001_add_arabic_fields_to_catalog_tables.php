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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
        });

        Schema::table('meals', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->text('description_ar')->nullable()->after('description');
            $table->string('includes_ar')->nullable()->after('includes');
            $table->text('how_to_use_ar')->nullable()->after('how_to_use');
            $table->text('features_ar')->nullable()->after('features');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->text('description_ar')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('meals', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'description_ar', 'includes_ar', 'how_to_use_ar', 'features_ar']);
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'description_ar']);
        });
    }
};
