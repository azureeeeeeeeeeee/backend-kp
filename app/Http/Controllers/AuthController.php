<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\OTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="User login",
     *     description="Authenticate a user and return a token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User berhasil login"),
     *     @OA\Response(response=404, description="Invalid credentials"),
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'message' => "user berhasil login",
            'token' => $token,
        ];

        return response()->json($data, 200)->cookie('TOKENID', $token, 1440, '/', null, false, true);
    }




    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="User registration",
     *     description="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password", "name", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User berhasil login"),
     *     @OA\Response(response=404, description="Invalid credentials"),
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'optional|string|max:20|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => "user",
        ]);

        $data = [
            'message' => "User registered successfully",
        ];
        return response()->json($data, 201);
    }






    /**
     * @OA\Post(
     *     path="/api/auth/send-otp",
     *     tags={"Auth"},
     *     summary="Send OTP to send url to change user's password",
     *     description="Sending URL to change password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email",},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Link ubah password sudah dikirim ke email yang diberikan"),
     *     @OA\Response(response=404, description="Email tidak ditemukan"),
     * )
     */
    public function sendOtp(Request $request) {
        $request->validate([
            'email' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email tidak ditemukan'
            ], 404);
        }

        $token = Str::uuid();

        $otp = OTP::create([
            'user_id' => $user->id,
            'token' => $token
        ]);

        Mail::to($user->email)->send(new OtpMail($token));

        return response()->json([
            'message' => 'Link ubah password sudah dikirim ke email yang diberikan',
        ], 200);
    }




    /**
     * @OA\Post(
     *     path="/api/auth/forgot-password",
     *     tags={"Auth"},
     *     summary="Change User Password",
     *     description="Changin Current user password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="uuid"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password berhasil diubah"),
     *     @OA\Response(response=404, description="Token yang diberikan tidak valid"),
     * )
     */
    public function changePasswords(Request $request) {
        $request->validate([
            'password' =>  'string|required|min:8|confirmed',
            'token' => 'string|required'
        ]);

        $otp = OTP::where('token', $request->token)->first();

        if (!$otp) {
            return response()->json([
                "message" => "Token yang diberikan tidak valid"
            ], 404);
        }

        $user = $otp->user;
        $user->password = Hash::make($request->password);
        $otp->delete();

        $user->save();

        return response()->json([
            "message" => "Password berhasil diubah"
        ], 200);
    }


    /**
     * @OA\Put(
     *     path="/api/auth/profile",
     *     tags={"Auth"},
     *     summary="Edit user profile",
     *     description="Update user profile information (requires authentication)",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Updated"),
     *             @OA\Property(property="nip", type="string", example="1234567890"),
     *             @OA\Property(property="email", type="string", format="email", example="newmail@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profil berhasil diperbarui"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validasi gagal"),
     * )
     */
    public function editProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'nip' => 'nullable|string|max:20|unique:users,nip,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update basic info
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['nip'])) {
            $user->nip = $validated['nip'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        // Update password if provided
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Optional: handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ], 200);
    }
}
