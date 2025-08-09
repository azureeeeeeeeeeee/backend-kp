<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/news",
     *     summary="Create a news article",
     *     tags={"News"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content", "image"},
     *                 @OA\Property(property="title", type="string", maxLength=255, minLength=8, example="My First News"),
     *                 @OA\Property(property="content", type="string", minLength=100, example="This is the content of the news..."),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="News created successfully"),
     *     @OA\Response(response=403, description="Forbidden - Admin only"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function create(Request $request) {
        if (Gate::denies('create', News::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }

        $request->validate([
            "title" => "required|string|max:255|min:8",
            "content" => "required|string|min:100",
            'image' => 'required|image|mimes:jpeg,png,jpg,gif'
        ]);

        $path = $request->file('image')->store('news', 'public');

        $news = new News();
        $news->title = $request->title;
        $news->content = $request->content;
        $news->thumbnail = $path;
        $news->save();

        return response()->json([
            'message' => 'News created successfully',
            'data'    => $news,
        ]);
    }





    /**
     * @OA\Put(
     *     path="/api/news/{id}",
     *     summary="Update a news article",
     *     tags={"News"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="News ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content"},
     *                 @OA\Property(property="title", type="string", maxLength=255, minLength=8, example="Updated News Title"),
     *                 @OA\Property(property="content", type="string", minLength=100, example="Updated content of the news..."),
     *                 @OA\Property(property="image", type="string", format="binary", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="News updated successfully"),
     *     @OA\Response(response=404, description="News not found"),
     *     @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function update(Request $request, $id) {
        if (Gate::denies('update', News::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        };
        
        $news = News::find($id);
        
        if (!$news) {
            return response()->json([
                "message" => "Berita dengan id $id tidak ditemukan"
            ], 404);
        };
        
        $data = $request->validate([
            "title" => "required|string|max:255|min:8",
            "content" => "required|string|min:100",
            'image' => 'nullable|image|mimes:jpeg,png,jpg'
        ]);
        
        $news->title = $data['title'];
        $news->content = $data['content'];
        
        if ($request->hasFile('image')) {
            if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail)) {
                Storage::disk('public')->delete($news->thumbnail);
            }
            
            $path = $request->file('image')->store('news', 'public');
            $news->thumbnail = $path;
        }
        $news->save();
        
        return response()->json([
            'message'=> "Berita berhasil diupdate"
        ]);
    }






    


    /**
     * @OA\Delete(
     *     path="/api/news/{id}",
     *     summary="Delete a news article",
     *     tags={"News"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="News ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="News deleted successfully"),
     *     @OA\Response(response=404, description="News not found"),
     *     @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function destroy($id) {
        if (Gate::denies("delete", News::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        }

        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'message' => "Berita dengan id $id tidak ditemukan"
            ], 404);
        }

        if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail)) {
            Storage::disk('public')->delete($news->thumbnail);
        }

        $news->delete();

        return response()->json([
            "message" => "Berita dengan id $id berhasil dihapus"
        ]);
    }










    /**
     * @OA\Get(
     *     path="/api/news/{id}",
     *     summary="Get a specific news article",
     *     tags={"News"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="News ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="News found"),
     *     @OA\Response(response=404, description="News not found")
     * )
     */
    public function show($id) {
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                "message" => "Berita dengan id $id tidak ditemukan"
            ]);
        }

        return response()->json([
            "message" => "Berita dengan id $id berhasil ditemukan",
            "berita" => $news
        ]);
    }








    /**
     * @OA\Get(
     *     path="/api/news",
     *     summary="Get paginated list of news",
     *     tags={"News"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="List of news")
     * )
     */
    public function index() {
        $news = News::orderBy("id","desc")->paginate(10);
        return response()->json([
            'message' => 'Berhasil mengambil semua daftar berita',
            'data' => $news
        ]);
    }
}
