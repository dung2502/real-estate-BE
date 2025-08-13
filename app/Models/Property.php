<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    // Cho phép gán các trường này qua mass assignment
    protected $fillable = [
        'title',
        'description',
        'property_type',
        'status',
        'price',
        'area',
        'bedrooms',
        'bathrooms',
        'floors',
        'address',
        'city',
        'district',
        'postal_code',
        'latitude',
        'longitude',
        'year_built',
        'features',
        'contact_name',
        'contact_phone',
        'contact_email',
        'created_by',
        'updated_by',
    ];

    // Ép kiểu dữ liệu cho JSON
    protected $casts = [
        'features' => 'array',
        'price'    => 'decimal:2',
        'area'     => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude'=> 'decimal:8',
    ];

    // Quan hệ: 1 Property có nhiều ảnh
    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

}
