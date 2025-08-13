<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'property_type' => 'required|in:apartment,house,villa,office,land',
            'status'        => 'required|in:available,sold,rented,pending',
            'price'         => 'required|numeric',
            'area'          => 'required|numeric',
            'bedrooms'      => 'nullable|integer|min:0',
            'bathrooms'     => 'nullable|integer|min:0',
            'floors'        => 'nullable|integer|min:1',
            'address'       => 'required|string',
            'city'          => 'required|string|max:100',
            'district'      => 'required|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'year_built'    => 'nullable|integer',
            'features'      => 'nullable|array',
            'features.*'    => 'sometimes|string',
            'contact_name'  => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'nullable|email|max:255',

            // upload nhiều ảnh
            'images'        => 'nullable|array',
            'images.*'      => 'file|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }
}
