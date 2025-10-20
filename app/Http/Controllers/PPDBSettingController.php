<?php

namespace App\Http\Controllers;

use App\Models\PPDBSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class PPDBSettingController extends Controller
{
    // GET: ambil status PPDB
    public function show()
    {
        try {
            $setting = PPDBSetting::first();
            
            if (!$setting) {
                $setting = PPDBSetting::create([
                    'status' => 'open',
                    'message' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status PPDB berhasil diambil',
                'data' => $setting
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status PPDB',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST: ubah status PPDB
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            // Cek apakah user adalah admin
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya admin yang bisa mengubah status PPDB'
                ], 403);
            }

            // Validasi request
            $validated = $request->validate([
                'status' => 'required|in:open,closed',
                'message' => 'nullable|string|max:500'
            ]);

            $setting = PPDBSetting::first();
            
            if (!$setting) {
                $setting = new PPDBSetting();
            }

            $setting->status = $validated['status'];
            $setting->message = $validated['message'] ?? null;
            
            if ($validated['status'] === 'open') {
                $setting->opened_at = now();
                $setting->closed_at = null;
            } else {
                $setting->closed_at = now();
            }
            
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'Status PPDB berhasil diubah',
                'data' => $setting
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status PPDB',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}