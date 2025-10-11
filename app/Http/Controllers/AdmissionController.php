<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;







/**
 * @OA\Schema(
 *   schema="Admission",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="full_name", type="string", example="Budi Santoso"),
 *   @OA\Property(property="place_of_birth", type="string", example="Samarinda"),
 *   @OA\Property(property="date_of_birth", type="string", format="date", example="2008-05-14"),
 *   @OA\Property(property="gender", type="string", example="laki-laki"),
 *   @OA\Property(property="address", type="string"),
 *   @OA\Property(property="religion", type="string", example="Islam"),
 *   @OA\Property(property="father_name", type="string"),
 *   @OA\Property(property="father_phone", type="string", nullable=true),
 *   @OA\Property(property="mother_name", type="string"),
 *   @OA\Property(property="mother_phone", type="string", nullable=true),
 *   @OA\Property(property="guardian_name", type="string", nullable=true),
 *   @OA\Property(property="guardian_phone", type="string", nullable=true),
 *   @OA\Property(property="paud", type="string", nullable=true),
 *   @OA\Property(property="file_kk", type="string", description="Storage path to KK file"),
 *   @OA\Property(property="file_akta", type="string", description="Storage path to Akta file"),
 *   @OA\Property(property="file_foto", type="string", description="Storage path to Foto file"),
 *   @OA\Property(property="status", type="string", example="pending"),
 *   @OA\Property(property="year", type="integer", example=2025),
 *   @OA\Property(property="admission_code", type="string", example="2025-BUDI-4832"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 *
 * @OA\Schema(
 *   schema="AdmissionCreateRequest",
 *   type="object",
 *   required={"full_name","place_of_birth","date_of_birth","gender","address","religion","father_name","mother_name","file_kk","file_akta","file_foto","year"},
 *   @OA\Property(property="full_name", type="string"),
 *   @OA\Property(property="place_of_birth", type="string"),
 *   @OA\Property(property="date_of_birth", type="string", format="date"),
 *   @OA\Property(property="gender", type="string", enum={"laki-laki","perempuan"}),
 *   @OA\Property(property="address", type="string"),
 *   @OA\Property(property="religion", type="string"),
 *   @OA\Property(property="father_name", type="string"),
 *   @OA\Property(property="father_phone", type="string"),
 *   @OA\Property(property="mother_name", type="string"),
 *   @OA\Property(property="mother_phone", type="string"),
 *   @OA\Property(property="guardian_name", type="string"),
 *   @OA\Property(property="guardian_phone", type="string"),
 *   @OA\Property(property="paud", type="string"),
 *   @OA\Property(property="file_kk", type="string", format="binary"),
 *   @OA\Property(property="file_akta", type="string", format="binary"),
 *   @OA\Property(property="file_foto", type="string", format="binary"),
 *   @OA\Property(property="year", type="integer")
 * )
 *
 * @OA\Schema(
 *   schema="AdmissionUpdateRequest",
 *   type="object",
 *   @OA\Property(property="full_name", type="string"),
 *   @OA\Property(property="place_of_birth", type="string"),
 *   @OA\Property(property="date_of_birth", type="string", format="date"),
 *   @OA\Property(property="gender", type="string", enum={"laki-laki","perempuan"}),
 *   @OA\Property(property="address", type="string"),
 *   @OA\Property(property="religion", type="string"),
 *   @OA\Property(property="father_name", type="string"),
 *   @OA\Property(property="father_phone", type="string"),
 *   @OA\Property(property="mother_name", type="string"),
 *   @OA\Property(property="mother_phone", type="string"),
 *   @OA\Property(property="guardian_name", type="string"),
 *   @OA\Property(property="guardian_phone", type="string"),
 *   @OA\Property(property="paud", type="string"),
 *   @OA\Property(property="file_kk", type="string", format="binary"),
 *   @OA\Property(property="file_akta", type="string", format="binary"),
 *   @OA\Property(property="file_foto", type="string", format="binary"),
 *   @OA\Property(property="status", type="string", enum={"pending","diterima","ditolak"}),
 *   @OA\Property(property="year", type="integer")
 * )
 *
 * @OA\Tag(
 *   name="Admissions",
 *   description="Admissions management"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
class AdmissionController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/admissions",
     *   tags={"Admissions"},
     *   summary="List admissions (paginated)",
     *   description="Get paginated list of admissions. Admin only.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="data", type="object")
     *     )
     *   ),
     *   @OA\Response(response=403, description="Admin only")
     * )
     */
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


















    /**
     * @OA\Get(
     *   path="/api/admissions/{id}",
     *   tags={"Admissions"},
     *   summary="Get admission by ID",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Successful", @OA\JsonContent(
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="data", ref="#/components/schemas/Admission")
     *   )),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=403, description="Admin only")
     * )
     */
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
    
    
    
    
    
    


    /**
     * @OA\Post(
     *   path="/api/admissions",
     *   tags={"Admissions"},
     *   summary="Create admission",
     *   description="Create a new admission. Uploads files (kk, akta, foto).",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/AdmissionCreateRequest")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="data", ref="#/components/schemas/Admission")
     *   )),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
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









    /**
     * @OA\Put(
     *   path="/api/admissions/{id}",
     *   tags={"Admissions"},
     *   summary="Update admission",
     *   description="Update existing admission. Admin only.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/AdmissionUpdateRequest")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Updated", @OA\JsonContent(
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="data", ref="#/components/schemas/Admission")
     *   )),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=403, description="Admin only")
     * )
     */
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










    /**
     * @OA\Delete(
     *   path="/api/admissions/{id}",
     *   tags={"Admissions"},
     *   summary="Delete admission",
     *   description="Delete admission and its files. Admin only.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=403, description="Admin only")
     * )
     */
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
    







    /**
     * @OA\Get(
     *   path="/api/admissions/check/{code}",
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







    /**
     * @OA\Get(
     *   path="/api/admissions/data/filter",
     *   tags={"Admissions"},
     *   summary="Filter admissions by status and year, sorted alphabetically",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="year", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Successful", @OA\JsonContent(type="object"))
     * )
     */
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
