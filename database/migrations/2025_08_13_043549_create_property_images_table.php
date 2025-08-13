<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('property_id');
            $table->string('image_path', 500)->comment('Đường dẫn hình ảnh');
            $table->string('image_name', 255)->comment('Tên file');
            $table->boolean('is_primary')->default(false)->comment('Hình ảnh chính');
            $table->integer('sort_order')->default(0)->comment('Thứ tự sắp xếp');

            $table->timestamps();

            // Khóa ngoại
            $table->foreign('property_id')
                  ->references('id')->on('properties')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('property_images');
    }
};
