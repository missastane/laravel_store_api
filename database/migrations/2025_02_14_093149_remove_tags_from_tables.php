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
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
        Schema::table('post_categories', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
