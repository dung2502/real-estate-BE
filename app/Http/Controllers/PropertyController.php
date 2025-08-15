<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyStoreRequest;
use App\Http\Requests\PropertyUpdateRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyResourceFor1Room;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::query()->with(['images' => function ($q) {
            $q->orderByDesc('is_primary')->orderBy('sort_order');
        }]);

        if ($request->filled('city')) {
            $query->where('city', $request->string('city'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        $sort  = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $allowedSorts = ['price','area','created_at','title','city','status'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $order);
        $perPage = (int) $request->input('per_page', 10);
        $paginator = $query->paginate($perPage)->appends($request->query());
        return response()->json([
            'data' => PropertyResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function store(PropertyStoreRequest $request)
    {
        $validated = $request->validated();
        if ($request->user()) {
            $validated['created_by'] = $request->user()->id;
            $validated['updated_by'] = $request->user()->id;
        }

        DB::beginTransaction();
        try {
            $property = Property::create($validated);

            if ($request->hasFile('images')) {
                $cloudinary = new Cloudinary(getenv('CLOUDINARY_URL'));

                foreach ($request->file('images') as $idx => $file) {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder' => 'properties',
                            'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                            'overwrite' => true
                        ]
                    );

                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image_path'  => $uploadResult['secure_url'],
                        'image_name'  => $file->getClientOriginalName(),
                        'is_primary'  => $idx === 0,
                        'sort_order'  => $idx,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Property created successfully',
                'data'    => (new PropertyResource($property->load('images')))
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        $property = Property::with(['images' => function ($q) {
            $q->orderByDesc('is_primary')->orderBy('sort_order');
        }])->findOrFail($id);

        return response()->json(new PropertyResource($property));
    }

   public function update(PropertyUpdateRequest $request, $id)
    {
        $property = Property::findOrFail($id);
        $validated = $request->validated();

        if ($request->user()) {
            $validated['updated_by'] = $request->user()->id;
        }

        DB::beginTransaction();
        try {
            // Cập nhật thông tin property
            $property->update($validated);

            // Upload thêm ảnh mới lên Cloudinary (nếu có)
            if ($request->hasFile('images')) {
                $cloudinary = new Cloudinary(getenv('CLOUDINARY_URL'));

                $startOrder = (int) ($property->images()->max('sort_order') ?? 0) + 1;

                foreach ($request->file('images') as $i => $file) {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder'     => 'properties',
                            'public_id'  => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                            'overwrite'  => true
                        ]
                    );

                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image_path'  => $uploadResult['secure_url'],
                        'image_name'  => $file->getClientOriginalName(),
                        'is_primary'  => false,
                        'sort_order'  => $startOrder + $i,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Property updated successfully',
                'data'    => (new PropertyResource($property->load('images')))
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();
        return response()->json(['message' => 'Property deleted successfully']);
    }

    public function restore($id)
    {
        $property = Property::onlyTrashed()->findOrFail($id);
        $property->restore();
        return response()->json(['message' => 'Property restored successfully']);
    }

    public function getImagesByPropertyId($id)
    {
        $images = PropertyImage::where('property_id', $id)->get()->map(function ($image) {
            return $image;
        });

        return response()->json([
            'property_id' => $id,
            'images' => $images
        ]);
    }


}
