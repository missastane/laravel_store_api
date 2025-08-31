<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->text('description');
            $table->tinyInteger('author')->default(2)->comment('2 => customer, 1 => admin');
            $table->tinyInteger('seen')->default(2)->comment('2 => unseen, 1 => seen');
            $table->tinyInteger('status')->default(2)->comment('2 => inactive, 1 => active');
            $table->foreignId('reference_id')->nullable()->constrained('ticket_admins')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('category_id')->constrained('ticket_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('priority_id')->constrained('ticket_priorities')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('tickets');
    }
}
