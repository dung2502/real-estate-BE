<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'property_type' => $this->property_type,
            'status'        => $this->status,
            'price'         => (float) $this->price,
            'area'          => (float) $this->area,
            'bedrooms'      => $this->bedrooms,
            'bathrooms'     => $this->bathrooms,
            'floors'        => $this->floors,
            'address'       => $this->address,
            'city'          => $this->city,
            'district'      => $this->district,
            'postal_code'   => $this->postal_code,
            'latitude'      => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude'     => $this->longitude !== null ? (float) $this->longitude : null,
            'year_built'    => $this->year_built,
            'features'      => $this->features ?? [],
            'contact_name'  => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            
            // Nếu chưa load images hoặc null thì trả về mảng rỗng
            'images'        => PropertyImageResource::collection($this->images ?? collect()),

            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
        ];
    }
}
