<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Type\Integer;

class GalleryController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/gallery",
     *     tags={"Gallery"},
     *     summary="Create a new gallery activity",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","activity_date"},
     *             @OA\Property(property="name", type="string", example="Lomba 17 Agustus"),
     *             @OA\Property(property="description", type="string", example="Deskripsi lomba 17 agustus"),
     *             @OA\Property(property="activity_date", type="string", format="date", example="2025-08-10")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Galeri kegiatan baru berhasil dibuat"),
     *     @OA\Response(response=403, description="Admin only")
     * )
     */
    public function addActivity(Request $request) {
        if (Gate::denies('create', Gallery::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        $data = $request->validate([
            "name" => "required|string|min:8",
            "description" => "required|string|min:20|max:500",
            "activity_date" => "required|date"
        ]);

        Gallery::create($data);

        return response()->json([
            "message" => "Galeri kegiatan baru berhasil dibuat"
        ]);
    }





    /**
     * @OA\Delete(
     *     path="/api/gallery/{id}",
     *     tags={"Gallery"},
     *     summary="Delete a gallery activity",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(response=200, description="Galeri kegiatan berhasil dihapus"),
     *     @OA\Response(response=403, description="Admin only"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function deleteActivity(Request $request, $id) {
        if (Gate::denies('delete', Gallery::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }

        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json([
                "message" => "Galeri aktivitas dengan id $id tidak ditemukan"
            ]);
        }
        $gallery->delete();

        return response()->json([
            "message" => "Galeri kegiatan dengan id $id berhasil dihapus"
        ]);
    }
    




    /**
     * @OA\Put(
     *     path="/api/gallery/{id}",
     *     tags={"Gallery"},
     *     summary="Edit a gallery activity",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Activity Name"),
     *             @OA\Property(property="description", type="string",  example="Deskripsi lomba 17 agustus"),
     *             @OA\Property(property="activity_date", type="string", format="date", example="2025-08-15")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Galeri kegiatan berhasil diubah"),
     *     @OA\Response(response=403, description="Admin only"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function editActivity(Request $request, $id) {
        if (Gate::denies('update', Gallery::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        
        $gallery = Gallery::find($id);
        
        if (!$gallery) {
            return response()->json([
                "message" => "Galery aktivitas dengan id $id tidak ditemukan"
            ]);
        }
        
        $data = $request->validate([
            "name" => "nullable|string|min:8",
            "description" => "nullable|string|min:20|max:300",
            "activity_date" => "nullable|date"
        ]);
        
        $gallery->update($data);
        
        return response()->json([
            "message" => "Galeri kegiatan dengan id $id berhasil diubah"
        ]);
    }
    





    /**
     * @OA\Get(
     *     path="/gallery/{id}",
     *     tags={"Gallery"},
     *     summary="Get a single gallery activity with its media",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(response=200, description="Galeri kegiatan ditemukan"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function getSingleActivity(Request $request, $id) {
        $gallery = Gallery::with('images')->find($id);
        
        if (!$gallery) {
            return response()->json([
                "message" => "Galeri aktivitas dengan id $id tidak ditemukan"
            ]);
        }

        return response()->json([
            "message" => "Galeri kegiatan berhasil ditemukan",
            "galeri" => $gallery
        ]);
    }




    /**
     * @OA\Get(
     *     path="/gallery",
     *     tags={"Gallery"},
     *     summary="Get all gallery activities sorted by activity date (latest first)",
     *     @OA\Response(response=200, description="Semua galeri kegiatan berhasil ditemukan")
     * )
     */
    public function getAllActivity(Request $request) {
        $galleries = Gallery::with('images')->orderBy('activity_date','desc')->get();

        return response()->json([
            'message' => 'Semua galeri kegiatan berhasil ditemukan',
            "galeri" => $galleries
        ]);
    }






    /**
     * @OA\Post(
     *     path="/gallery/{id}/media",
     *     tags={"Gallery"},
     *     summary="Add media (image/video) to a gallery",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"media"},
     *                 @OA\Property(property="media", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Media berhasil ditambahkan"),
     *     @OA\Response(response=403, description="Admin only"),
     *     @OA\Response(response=404, description="Gallery not found")
     * )
     */
    public function addImage(Request $request, $id) {
        if (Gate::denies('create', Gallery::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        
        $gallery = Gallery::with('images')->find($id);
        
        if (!$gallery) {
            return response()->json([
                "message" => "Galeri aktivitas dengan id $id tidak ditemukan"
            ]);
        }
        
        $request->validate([
            'media' => 'required|mimes:jpg,jpeg,png,mp4,mov,avi'
        ]);
        
        $path = $request->file('media')->store('gallery', 'public');
        $media = $gallery->images()->create([
            'path' => $path,
        ]);
        
        return response()->json([
            "message" => "Media berhasil ditambahkan"
        ]);
    }
    






    /**
     * @OA\Delete(
     *     path="/gallery/{id}/media",
     *     tags={"Gallery"},
     *     summary="Delete media from gallery",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Response(response=200, description="Media berhasil dihapus"),
     *     @OA\Response(response=403, description="Admin only"),
     *     @OA\Response(response=404, description="Media not found")
     * )
     */
    public function deleteImage(Request $request, $id) {
        if (Gate::denies('create', Gallery::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }

        $media = GalleryImage::find($id);
        if (!$media) {
            return response()->json([
                'message' => "Media dengan id $id tidak ditemukan"
            ], 404);
        }

        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media berhasil dihapus'
        ]);
    }
}
