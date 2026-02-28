<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdmissionController extends Controller
{
    public function index()
    {
        // Hanya admin yang bisa lihat semua data
        if (Gate::denies('viewAny', Admission::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = Admission::all();
        return response()->json($data);
    }

    public function show($id)
    {
        // Semua orang boleh lihat detail pendaftaran (misalnya untuk cek status)
        $admission = Admission::findOrFail($id);
        return response()->json($admission);
    }

    public function store(Request $request)
    {
        // Semua orang boleh daftar tanpa login
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'address' => 'required|string',
            'religion' => 'required|string',
            'father_name' => 'required|string|max:255',
            'father_phone' => 'nullable|string|max:15',
            'mother_name' => 'required|string|max:255',
            'mother_phone' => 'nullable|string|max:15',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:15',
            'paud' => 'nullable|string|max:255',
            'file_kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_akta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_foto' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // simpan file kalau ada
        if ($request->hasFile('file_kk')) {
            $validated['file_kk'] = $request->file('file_kk')->store('ppdb/kk', 'public');
        }
        if ($request->hasFile('file_akta')) {
            $validated['file_akta'] = $request->file('file_akta')->store('ppdb/akta', 'public');
        }
        if ($request->hasFile('file_foto')) {
            $validated['file_foto'] = $request->file('file_foto')->store('ppdb/foto', 'public');
        }

        $validated['status'] = 'pending';
        $validated['year'] = date('Y');
        $validated['admission_code'] = 'ADM-' . strtoupper(uniqid());

        $admission = Admission::create($validated);

        return response()->json([
            'message' => 'Pendaftaran berhasil dikirim',
            'data' => $admission
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $admission = Admission::findOrFail($id);

        // hanya admin yang boleh update/verifikasi
        if (Gate::denies('update', $admission)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,diterima,ditolak',
        ]);


        $admission->update($validated);

        return response()->json([
            'message' => 'Data berhasil diperbarui',
            'data' => $admission
        ]);
    }

    public function destroy($id)
    {
        $admission = Admission::findOrFail($id);

        // hanya admin yang boleh hapus
        if (Gate::denies('delete', $admission)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admission->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }







    /**
     * @OA\Get(
     *   path="/api/admissions/{code}/check",
     *   tags={"Admissions"},
     *   summary="Check admission status by code",
     *   @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Found", @OA\JsonContent(
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="status", type="string"),
     *       @OA\Property(property="fullname", type="string")
     *   )),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function checkAdmissionStatus($code) {
        $admission = Admission::where('admission_code', $code)->first();

        if (!$admission) {
            return response()->json(['message' => 'Kode pendaftaran tidak ditemukan'], 404);
        }

        return response()->json([
            'full_name' => $admission->full_name,
            'status' => $admission->status,
            'year' => $admission->year
        ]);
    }

    public function filter(Request $request)
    {
        // hanya admin yang bisa filter data
        if (Gate::denies('viewAny', Admission::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $year = $request->query('year');
        $status = $request->query('status');

        $query = Admission::query();
        if ($year) $query->where('year', $year);
        if ($status) $query->where('status', $status);

        return response()->json($query->get());
    }
}
