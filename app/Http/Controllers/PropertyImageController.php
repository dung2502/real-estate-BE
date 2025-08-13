<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyImageController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'images'   => 'required|array|min[1]',
            'images.*' => 'file|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $property = Property::findOrFail($id);

        DB::beginTransaction();
        try {
            $startOrder = (int) ($property->images()->max('sort_order') ?? 0) + 1;

            foreach ($request->file('images') as $i => $file) {
                $path = $file->store('properties', 'public');
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path'  => '/storage/' . $path,
                    'image_name'  => $file->getClientOriginalName(),
                    'is_primary'  => false,
                    'sort_order'  => $startOrder + $i,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Images uploaded successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Upload failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($propertyId, $imageId)
    {
        $image = PropertyImage::where('property_id', $propertyId)->findOrFail($imageId);

        // Xóa file vật lý (an toàn khi path dạng /storage/..)
        $relativePath = ltrim(str_replace('/storage/', '', $image->image_path), '/');
        if ($relativePath) {
            Storage::disk('public')->delete($relativePath);
        }

        $image->delete();
        return response()->json(['message' => 'Image deleted successfully']);
    }
}
