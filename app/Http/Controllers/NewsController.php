<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    //
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









    public function index() {
        $news = News::orderBy("id","desc")->paginate(10);
        return response()->json([
            'message' => 'Berhasil mengambil semua daftar berita',
            'data' => $news
        ]);
    }
}
