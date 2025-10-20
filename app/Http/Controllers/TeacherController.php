<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherController extends Controller
{
    // GET semua guru
    public function index()
    {
        $teachers = Teacher::orderBy('created_at', 'desc')->get();
        return response()->json([
            'message' => 'Semua data guru berhasil diambil',
            'data' => $teachers
        ], 200);
    }

    // GET guru by ID
    public function show($id)
    {
        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json([
                'message' => "Guru dengan id $id tidak ditemukan"
            ], 404);
        }
        return response()->json([
            'message' => "Guru dengan id $id berhasil ditemukan",
            'data' => $teacher
        ], 200);
    }

    // POST - Tambah guru
    public function store(Request $request)
    {
        if (Gate::denies('create', Teacher::class)) {
            return response()->json(['error' => 'Admin only'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'major' => 'required|string|min:3|max:100',
            'phone' => 'required|string|unique:teachers,phone',
            'status' => 'required|in:aktif,tidak_aktif,cuti',
            'position' => 'required|string|min:3|max:100'
        ]);

        $teacher = Teacher::create($validated);

        return response()->json([
            'message' => 'Guru berhasil ditambahkan',
            'data' => $teacher
        ], 201);
    }

    // PUT - Update guru
    public function update(Request $request, $id)
    {
        if (Gate::denies('update', Teacher::class)) {
            return response()->json(['error' => 'Admin only'], 403);
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json([
                'message' => "Guru dengan id $id tidak ditemukan"
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|min:3|max:100',
            'major' => 'nullable|string|min:3|max:100',
            'phone' => 'nullable|string|unique:teachers,phone,' . $id,
            'status' => 'nullable|in:aktif,tidak_aktif,cuti',
            'position' => 'nullable|string|min:3|max:100'
        ]);

        $teacher->update(array_filter($validated));

        return response()->json([
            'message' => 'Guru berhasil diupdate',
            'data' => $teacher
        ], 200);
    }

    // DELETE - Hapus guru
    public function destroy($id)
    {
        if (Gate::denies('delete', Teacher::class)) {
            return response()->json(['error' => 'Admin only'], 403);
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json([
                'message' => "Guru dengan id $id tidak ditemukan"
            ], 404);
        }

        $teacher->delete();

        return response()->json([
            'message' => "Guru dengan id $id berhasil dihapus"
        ], 200);
    }
}