<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => 'sometimes|string|max:255',
            'description'   => 'sometimes|nullable|string',
            'property_type' => 'sometimes|in:apartment,house,villa,office,land',
            'status'        => 'sometimes|in:available,sold,rented,pending',
            'price'         => 'sometimes|numeric',
            'area'          => 'sometimes|numeric',
            'bedrooms'      => 'sometimes|integer|min:0',
            'bathrooms'     => 'sometimes|integer|min:0',
            'floors'        => 'sometimes|integer|min:1',
            'address'       => 'sometimes|string',
            'city'          => 'sometimes|string|max:100',
            'district'      => 'sometimes|string|max:100',
            'postal_code'   => 'sometimes|nullable|string|max:20',
            'latitude'      => 'sometimes|nullable|numeric',
            'longitude'     => 'sometimes|nullable|numeric',
            'year_built'    => 'sometimes|nullable|integer',
            'features'      => 'sometimes|nullable|array',
            'features.*'    => 'sometimes|string',
            'contact_name'  => 'sometimes|string|max:255',
            'contact_phone' => 'sometimes|string|max:20',
            'contact_email' => 'sometimes|nullable|email|max:255',

            'images'        => 'sometimes|array',
            'images.*'      => 'file|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }
}
