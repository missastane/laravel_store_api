<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('introduction');
            $table->bigInteger('view')->default(0);
            $table->string('slug')->unique()->nullable();
            $table->text('image');
            $table->decimal('weight', 10, 2);
            $table->decimal('length', 10, 1)->comment('unit = cm');
            $table->decimal('width', 10, 1)->comment('unit = cm');
            $table->decimal('height', 10, 1)->comment('unit = cm');
            $table->decimal('price', 20, 3);
            $table->tinyInteger('status')->default(2)->comment('2 => inactive, 1 => active');
            $table->tinyInteger('marketable')->default(1)->comment('(marketable means this product is available to sell now :)2 => not marketable, 1 => marketable');
            $table->string('tags');
            $table->string('related_products')->nullable();
            $table->tinyInteger('sold_number')->default(2);
            $table->tinyInteger('frozen_number')->default(2);
            $table->tinyInteger('marketable_number')->default(2);
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('published_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
