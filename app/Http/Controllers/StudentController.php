<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/student",
     *     summary="Get paginated list of students",
     *     tags={"Students"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="List of students"),
     *     @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function index()
    {
        if (Gate::denies('viewAny', Student::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        $students = Student::latest()->paginate(10);

        return response()->json([
            'message' => 'berhasil fetch data siswa',
            'siswa' => $students 
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/student",
     *     summary="Create a new student",
     *     tags={"Students"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={
     *                     "full_name","place_of_birth","nisn","date_of_birth",
     *                     "gender","address","religion","father_name","mother_name",
     *                     "file_foto","year"
     *                 },
     *                 @OA\Property(property="full_name", type="string", example="John Doe"),
     *                 @OA\Property(property="place_of_birth", type="string", example="Jakarta"),
     *                 @OA\Property(property="nisn", type="string", example="1234567890"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="2010-03-15"),
     *                 @OA\Property(property="gender", type="string", enum={"laki-laki","perempuan"}, example="laki-laki"),
     *                 @OA\Property(property="address", type="string", example="Jl. Merdeka No.10"),
     *                 @OA\Property(property="religion", type="string", example="Islam"),
     *                 @OA\Property(property="father_name", type="string", example="Budi"),
     *                 @OA\Property(property="mother_name", type="string", example="Siti"),
     *                 @OA\Property(property="file_foto", type="string", format="binary"),
     *                 @OA\Property(property="year", type="integer", example=2025)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Student created successfully"),
     *     @OA\Response(response=403, description="Forbidden - Admin only"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        if (Gate::denies('create', Student::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'nisn' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:laki-laki,perempuan',
            'address' => 'required|string',
            'religion' => 'required|string',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'file_foto' => 'required|file|image|max:5120',
            'year' => 'required|digits:4',
        ]);

        $validated['nis'] = Student::generateNIS($validated['year']);

        $path = $request->file('file_foto')->store('students', 'public');
        $validated['file_foto'] = $path;

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Siswa berhasil ditambahkan.',
            'siswa' => $student
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/student/{nis}",
     *     summary="Get student by NIS",
     *     tags={"Students"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="nis",
     *         in="path",
     *         required=true,
     *         description="Student NIS",
     *         @OA\Schema(type="string", example="2025-001")
     *     ),
     *     @OA\Response(response=200, description="Student found"),
     *     @OA\Response(response=404, description="Student not found"),
     *     @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function show($nis)
    {
        if (Gate::denies('view', Student::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        $student = Student::where('nis', $nis)->firstOrFail();
        return response()->json([
            'message' => "Data siswa dengan NIS $nis berhasil didapatkan",
            'siswa' => $student
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/student/{nis}",
     *     summary="Update student data",
     *     tags={"Students"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="nis",
     *         in="path",
     *         required=true,
     *         description="Student NIS",
     *         @OA\Schema(type="string", example="2025-001")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nisn", type="string", example="1234567890"),
     *                 @OA\Property(property="full_name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="place_of_birth", type="string", example="Samarinda"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="2010-03-15"),
     *                 @OA\Property(property="gender", type="string", enum={"laki-laki","perempuan"}, example="perempuan"),
     *                 @OA\Property(property="address", type="string", example="Jl. Baru No.20"),
     *                 @OA\Property(property="religion", type="string", example="Kristen"),
     *                 @OA\Property(property="father_name", type="string", example="Andi"),
     *                 @OA\Property(property="mother_name", type="string", example="Rina"),
     *                 @OA\Property(property="file_foto", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="year", type="integer", example=2025)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Student updated successfully"),
     *     @OA\Response(response=404, description="Student not found"),
     *     @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function update(Request $request, $nis)
    {
        if (Gate::denies('update', Student::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        $student = Student::where('nis', $nis)->firstOrFail();

        $validated = $request->validate([
            'nisn' => ['sometimes', 'string', Rule::unique('students')->ignore($student->id)],
            'full_name' => 'sometimes|string|max:255',
            'place_of_birth' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'gender' => ['sometimes', Rule::in(['laki-laki', 'perempuan'])],
            'address' => 'sometimes|string',
            'religion' => 'sometimes|string',
            'father_name' => 'sometimes|string',
            'mother_name' => 'sometimes|string',
            'file_foto' => 'sometimes|file|mimes:jpg,jpeg,png|max:5120',
            'year' => 'sometimes|digits:4|integer',
        ]);

        if ($request->hasFile('file_foto')) {
            if ($student->file_foto && Storage::disk('public')->exists($student->file_foto)) {
                Storage::disk('public')->delete($student->file_foto);
            }

            $path = $request->file('file_foto')->store('students', 'public');
            $validated['file_foto'] = $path;
        }

        $student->update($validated);

        return response()->json([
            'message' => 'Data siswa berhasil diperbarui',
            'data' => $student
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/student/{nis}",
     *     summary="Delete a student",
     *     tags={"Students"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="nis",
     *         in="path",
     *         required=true,
     *         description="Student NIS",
     *         @OA\Schema(type="string", example="2025-001")
     *     ),
     *     @OA\Response(response=200, description="Student deleted successfully"),
     *     @OA\Response(response=404, description="Student not found"),
     *     @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function destroy($nis)
    {
        if (Gate::denies('delete', Student::class)) {
            return response()->json([
                "error" => "Admin only"
            ], 403);
        }
        $student = Student::where('nis', $nis)->firstOrFail();

        if ($student->file_foto && Storage::disk('public')->exists($student->file_foto)) {
            Storage::disk('public')->delete($student->file_foto);
        }

        $student->delete();

        return response()->json(['message' => 'Data siswa berhasil dihapus']);
    }
}
