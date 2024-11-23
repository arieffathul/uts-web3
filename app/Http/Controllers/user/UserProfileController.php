<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data profil pengguna
        $profiles = UserProfile::with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $profiles,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|unique:user_profiles,user_id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'no_telp' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = new UserProfile($request->all());

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('profiles', 'public'); // Simpan gambar ke folder profiles di disk public
            $profile->image = $path; // Simpan path gambar ke database
        }

        $profile->save();

        // Update name di tabel users
        $profile->user->update(['name' => $profile->name]);

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully.',
            'data' => $profile,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Cari profil berdasarkan ID
        $profile = UserProfile::with('user')->find($id);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'no_telp' => 'sometimes|string|max:15',
            'alamat' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = UserProfile::find($id);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found.',
            ], 404);
        }

        if ($profile->user_id != $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this profile.',
            ], 403);
        }

        $profile->fill($request->except('image'));

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($profile->image) {
                Storage::disk('public')->delete($profile->image);
            }

            // Simpan gambar baru
            $path = $request->file('image')->store('profiles', 'public');
            $profile->image = $path;
            // dd($profile->image);
        }

        $profile->save();

        if ($request->has('name')) {
            $profile->user->update(['name' => $profile->name]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $profile,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $profile = UserProfile::find($id);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found.',
            ], 404);
        }

        // Hapus gambar jika ada
        if ($profile->image) {
            Storage::disk('public')->delete($profile->image);
        }

        // Hapus data profil
        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully.',
        ], 200);
    }

    public function checkProfile(Request $request)
    {
        $userId = $request->user()->id;

        // Cari profil berdasarkan user_id
        $profile = UserProfile::where('user_id', $userId)->first();

        if (!$profile) {
            return response()->json([
                'hasProfile' => false,
                'message' => 'User does not have a profile.',
            ], 200);
        }

        return response()->json([
            'hasProfile' => true,
            'profile' => $profile,
            'message' => 'User profile found.',
        ], 200);
    }
}
