<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => $user->avatar, 
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            // max:2048 agar batasnya benar-benar 2MB (2048 KB)
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        $user = $request->user(); 
        $file = $request->file('avatar');

        $fileName = 'avatar-' . $user->id . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();

        try {
            // Upload langsung ke Supabase Storage menggunakan driver S3
            $path = Storage::disk('s3')->putFileAs('', $file, $fileName, 'public');

            // Cek foto lama di kolom database 'avatar' agar Supabase tidak penuh sampah
            if ($user->avatar) {
                $oldFileName = basename($user->avatar);
                if (Storage::disk('s3')->exists($oldFileName)) {
                    Storage::disk('s3')->delete($oldFileName);
                }
            }

            Storage::disk('s3')->putFileAs('', $file, $fileName, 'public');

            // Dapatkan URL Publik dari Supabase
            $publicUrl = Storage::disk('s3')->url($fileName);

            // Simpan URL absolut tersebut ke kolom database 'avatar'
            $user->update([
                'avatar' => $publicUrl
            ]);

            $user->refresh();

            // Kembalikan key 'avatar' ke frontend sesuai response yang diharapkan
            return response()->json([
                'status'  => 'success',
                'message' => 'Foto profil berhasil diperbarui.',
                'avatar'  => $publicUrl
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengupload gambar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}