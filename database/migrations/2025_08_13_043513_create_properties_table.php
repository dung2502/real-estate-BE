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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255)->comment('Tiêu đề bất động sản');
            $table->text('description')->nullable()->comment('Mô tả chi tiết');
            $table->enum('property_type', ['apartment', 'house', 'villa', 'office', 'land'])
                  ->comment('Loại bất động sản');
            $table->enum('status', ['available', 'sold', 'rented', 'pending'])
                  ->default('available')
                  ->comment('Trạng thái');
            $table->decimal('price', 15, 2)->comment('Giá bán/cho thuê');
            $table->decimal('area', 10, 2)->comment('Diện tích (m²)');
            $table->integer('bedrooms')->default(0)->comment('Số phòng ngủ');
            $table->integer('bathrooms')->default(0)->comment('Số phòng tắm');
            $table->integer('floors')->default(1)->comment('Số tầng');
            $table->text('address')->comment('Địa chỉ');
            $table->string('city', 100)->comment('Thành phố');
            $table->string('district', 100)->comment('Quận/Huyện');
            $table->string('postal_code', 20)->nullable()->comment('Mã bưu điện');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Vĩ độ');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Kinh độ');
            $table->integer('year_built')->nullable()->comment('Năm xây dựng');
            $table->json('features')->nullable()->comment('Tính năng (JSON)');
            $table->json('images')->nullable()->comment('Danh sách hình ảnh (JSON)');
            $table->string('contact_name', 255)->comment('Tên liên hệ');
            $table->string('contact_phone', 20)->comment('Số điện thoại liên hệ');
            $table->string('contact_email', 255)->nullable()->comment('Email liên hệ');

            $table->unsignedBigInteger('created_by')->nullable()->comment('ID người tạo');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID người cập nhật');

            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
};
