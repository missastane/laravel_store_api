<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicMailFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_mail_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_mail_id')->constrained('public_mail')->onDelete('cascade')->onUpdate('cascade');
            $table->text('file_path');
            $table->bigInteger('file_size');
            $table->string('file_type');
            $table->timestamps();
            $table->tinyInteger('status')->default(2)->comment('2 => inactive, 1 => active');
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
        Schema::dropIfExists('public_mail_files');
    }
}
