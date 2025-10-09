<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdmissionController extends Controller
{
    //
    public function index() {
        if (Gate::denies("create", Admission::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        }
        $admissions = Admission::latest()->paginate(10);

        return response()->json([
            'message' => 'Berhasil mengambil data pendaftaran',
            'data' => $admissions
        ], 200);
    }





    public function show($id) {
        if (Gate::denies("create", Admission::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        }
        
        $admissions = Admission::find($id);
        
        if (!$admissions) {
            return response()->json([
                'message' => "Pendaftaran dengan ID $id tidak ditemukan"
            ], 404);
        }
        
        return response()->json([
            'message' => 'Pendaftaran dengan berhasil diambbil',
            'data' => $admissions
        ], 200);
    }
    
    
    
    
    
    
    public function store(Request $request) {
        // if (!$request->user()) {
        //     return response()->json([
        //         'error' => 'Admin only'
        //     ], 403);
        // }
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:laki-laki,perempuan',
            'address' => 'required|string',
            'religion' => 'required|string|max:50',
            'father_name' => 'required|string|max:255',
            'father_phone' => 'nullable|string|max:20',
            'mother_name' => 'required|string|max:255',
            'mother_phone' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'paud' => 'nullable|string|max:255',
            'file_kk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_akta' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_foto' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'year' => 'required|digits:4|integer|min:2000|max:' . date('Y'),
        ]);

        $kkPath = $request->file('file_kk')->store('admissions/kk', 'public');
        $aktaPath = $request->file('file_akta')->store('admissions/akta', 'public');
        $fotoPath = $request->file('file_foto')->store('admissions/foto', 'public');

        $randomNumber = random_int(1000, 9999);
        $namePart = strtoupper(Str::substr(Str::slug($request->full_name), 0, 5));
        do {
            $randomNumber = random_int(1000, 9999);
            $namePart = strtoupper(Str::substr(Str::slug($request->full_name), 0, 5));
            $code = date('Y') . '-' . $namePart . '-' . $randomNumber;
        } while (Admission::where('admission_code', $code)->exists());

        $admission = Admission::create([
            'full_name' => $validated['full_name'],
            'place_of_birth' => $validated['place_of_birth'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'religion' => $validated['religion'],
            'father_name' => $validated['father_name'],
            'father_phone' => $validated['father_phone'] ?? null,
            'mother_name' => $validated['mother_name'],
            'mother_phone' => $validated['mother_phone'] ?? null,
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'paud' => $validated['paud'] ?? null,
            'file_kk' => $kkPath,
            'file_akta' => $aktaPath,
            'file_foto' => $fotoPath,
            'year' => $validated['year'],
            'status' => 'pending',
            'admission_code' => $code,
        ]);

        return response()->json([
            'message' => 'Admission created successfully.',
            'data' => $admission,
        ], 201);
    }






    public function update(Request $request, int $id) {
        if (Gate::denies("create", Admission::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        }
        $admission = Admission::find($id);

        if (!$admission) {
            return response()->json([
                'message' => "Pendaftaran dengan ID $id tidak ditemukan"
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'place_of_birth' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:laki-laki,perempuan',
            'address' => 'sometimes|string',
            'religion' => 'sometimes|string|max:100',
            'father_name' => 'sometimes|string|max:255',
            'father_phone' => 'nullable|string|max:20',
            'mother_name' => 'sometimes|string|max:255',
            'mother_phone' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'paud' => 'nullable|string|max:255',
            'file_kk' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_akta' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_foto' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'status' => 'nullable|in:pending,diterima,ditolak',
            'year' => 'nullable|digits:4',
        ]);

        if ($request->hasFile('file_kk')) {
            if ($admission->file_kk) Storage::disk('public')->delete($admission->file_kk);
            $validated['file_kk'] = $request->file('file_kk')->store('admissions/kk', 'public');
        }

        if ($request->hasFile('file_akta')) {
            if ($admission->file_akta) Storage::disk('public')->delete($admission->file_akta);
            $validated['file_akta'] = $request->file('file_akta')->store('admissions/akta', 'public');
        }

        if ($request->hasFile('file_foto')) {
            if ($admission->file_foto) Storage::disk('public')->delete($admission->file_foto);
            $validated['file_foto'] = $request->file('file_foto')->store('admissions/foto', 'public');
        }

        $admission->update($validated);

        return response()->json([
            'message' => 'Admission updated successfully.',
            'data' => $admission,
        ]);
    }





    public function destroy(int $id) {
        if (Gate::denies("create", Admission::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        }
        $admission = Admission::find($id);

        if (!$admission) {
            return response()->json([
                'message' => "Pendaftaran dengan ID $id tidak ditemukan"
            ], 404);
        }

        foreach (['file_kk', 'file_akta', 'file_foto'] as $fileField) {
            if (!empty($admission->$fileField) && Storage::disk('public')->exists($admission->$fileField)) {
                Storage::disk('public')->delete($admission->$fileField);
            }
        }

        $admission->delete();

        return response()->json([
            'message' => 'Admission deleted successfully.'
        ], 200);
    }
    





    public function checkAdmissionStatus($code) {
        $admission = Admission::where('admission_code', $code)->first();
        if (!$admission) {
            return response()->json([
                'message' => "Pendaftaran dengan kode $code tidak ditemukan"
            ]);
        }

        return response()->json([
            'message' => "Berhasil mendapatkan status pendaftaran dengan kode $code",
            "status" => $admission->status,
            "fullname" => $admission->full_name
        ], 200);
    }   






    public function filter(Request $request)
    {
        if (Gate::denies("create", Admission::class)) {
            return response()->json([
                'error' => 'Admin only'
            ], 403);
        }
        $query = Admission::query();

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        if ($request->has('year') && $request->year !== null) {
            $query->where('year', $request->year);
        }

        $admissions = $query->orderBy('full_name', 'asc')->paginate(10);

        return response()->json($admissions);
    }
}
