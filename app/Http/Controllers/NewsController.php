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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
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
}
