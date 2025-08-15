<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Cloudinary\Cloudinary;

class PropertyImageController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $property = Property::findOrFail($id);

        DB::beginTransaction();
        try {
            $startOrder = (int) ($property->images()->max('sort_order') ?? 0) + 1;

            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            foreach ($request->input('images', []) as $i => $image) {
                if ($request->hasFile("images.$i")) {
                    $file = $request->file("images.$i");

                    // Upload lên Cloudinary
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        ['folder' => 'properties']
                    );

                    $imagePath = $uploadResult['secure_url']; // URL từ Cloudinary
                    $imageName = $file->getClientOriginalName();
                } else {
                    // Nếu đã có URL sẵn
                    $imagePath = $image;
                    $imageName = basename(parse_url($image, PHP_URL_PATH));
                }

                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path'  => $imagePath,
                    'image_name'  => $imageName,
                    'is_primary'  => false,
                    'sort_order'  => $startOrder + $i,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Images uploaded successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Upload failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($propertyId, $imageId)
    {
        $image = PropertyImage::where('property_id', $propertyId)->findOrFail($imageId);

        // Nếu ảnh nằm trên Cloudinary → xóa theo public_id
        if (filter_var($image->image_path, FILTER_VALIDATE_URL) &&
            str_contains($image->image_path, 'res.cloudinary.com')) {

            $publicId = pathinfo(parse_url($image->image_path, PHP_URL_PATH), PATHINFO_FILENAME);
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $cloudinary->uploadApi()->destroy("properties/{$publicId}");
        }

        $image->delete();
        return response()->json(['message' => 'Image deleted successfully']);
    }

    public function getImage($propertyId)
    {
        $images = PropertyImage::where('property_id', $propertyId)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->get(['id', 'image_path', 'image_name', 'is_primary', 'sort_order']);

        if ($images->isEmpty()) {
            return response()->json([
                'message' => 'No images found for this property.',
                'data'    => []
            ], 404);
        }

        return response()->json([
            'message' => 'Images retrieved successfully.',
            'data'    => $images
        ], 200);
    }
}
