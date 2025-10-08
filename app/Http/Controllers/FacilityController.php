<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FacilityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/facilities",
     *     tags={"Facilities"},
     *     summary="Get all facilities",
     *     description="Retrieve a paginated list of facilities ordered by latest updates.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of facilities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Berhasil mengambil semua daftar"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Ruang Kelas Lama"),
     *                         @OA\Property(property="path", type="string", example="facilities/abc123.jpg"),
     *                         @OA\Property(property="created_at", type="string", example="2025-10-08T05:00:00.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2025-10-08T05:10:00.000000Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="total", type="integer", example=25)
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $facilities = Facility::orderBy("updated_at", "desc")->paginate(10);
        return response()->json([
            "message" => "Berhasil mengambil semua daftar",
            "data" => $facilities
        ], 200);
    }
















    

    /**
     * @OA\Post(
     *     path="/api/facilities",
     *     tags={"Facilities"},
     *     summary="Create a new facility",
     *     description="Admin-only endpoint to create a new facility with image upload.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "image"},
     *                 @OA\Property(property="name", type="string", example="Ruang Kelas Lama"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Facility created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Berhasil menambahkan fasilitas"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ruang Kelas Lama"),
     *                 @OA\Property(property="path", type="string", example="facilities/xyz123.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden (Admin only)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        if (Gate::denies('create', Facility::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }

        $request->validate([
            "name" => "required|string|min:8|max:50",
            "image" => 'required|image|mimes:jpeg,png,jpg'
        ]);

        $path = $request->file('image')->store('facilities', 'public');

        $facility = new Facility();
        $facility->name = $request->name;
        $facility->path = $path;
        $facility->save();

        return response()->json([
            'message' => 'Berhasil menambahkan fasilitas',
            'data' => $facility
        ]);
    }

















    /**
     * @OA\Get(
     *     path="/api/facilities/{id}",
     *     tags={"Facilities"},
     *     summary="Get facility by ID",
     *     description="Retrieve a specific facility by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Facility ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facility found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Fasilitas dengan id 1 berhasil ditemukan"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ruang Kelas Lama"),
     *                 @OA\Property(property="path", type="string", example="facilities/abc.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Facility not found")
     * )
     */
    public function show($id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                "message" => "Fasilitas dengan id $id tidak ditemukan"
            ]);
        }

        return response()->json([
            "message" => "Fasilitas dengan id $id berhasil ditemukan",
            "data" => $facility
        ]);
    }














    /**
     * @OA\Post(
     *     path="/api/facilities/{id}",
     *     tags={"Facilities"},
     *     summary="Update facility by ID",
     *     description="Admin-only endpoint to update a facility. Image is optional.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Facility ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Ruang Kelas Baru"),
     *                 @OA\Property(property="image", type="string", format="binary", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Facility updated successfully"),
     *     @OA\Response(response=403, description="Forbidden (Admin only)"),
     *     @OA\Response(response=404, description="Facility not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        if (Gate::denies('update', Facility::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }

        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                "message" => "facility dengan id $id tidak ditemukan"
            ], 404);
        };

        $data = $request->validate([
            "name" => "required|string|min:8|max:50",
            "image" => 'nullable|image|mimes:jpeg,png,jpg'
        ]);

        $facility->name = $data['name'];

        if ($request->hasFile('image')) {
            if ($facility->path && Storage::disk('public')->exists($facility->path)) {
                Storage::disk('public')->delete($facility->path);
            }
            
            $path = $request->file('image')->store('facilities', 'public');
            $facility->path = $path;
        }
        $facility->save();

        return response()->json([
            'message' => 'Fasilitas berhasil diupdate',
        ]);
    }













    /**
     * @OA\Delete(
     *     path="/api/facilities/{id}",
     *     tags={"Facilities"},
     *     summary="Delete a facility",
     *     description="Admin-only endpoint to delete a facility by ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Facility ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Facility deleted successfully"),
     *     @OA\Response(response=403, description="Forbidden (Admin only)"),
     *     @OA\Response(response=404, description="Facility not found")
     * )
     */
    public function destroy($id)
    {
        if (Gate::denies("delete", Facility::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        }

        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'message' => "Fasilitas dengan id $id tidak ditemukan"
            ], 404);
        }

        if ($facility->path && Storage::disk('public')->exists($facility->path)) {
            Storage::disk('public')->delete($facility->path);
        }

        $facility->delete();

        return response()->json([
            "message" => "Fasilitas dengan id $id berhasil dihapus"
        ]);
    }
}
