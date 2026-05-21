<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function uploadAvatar(Request $request)
    {
        // 1. Validasi ketat di sisi backend (Wajib!)
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:500', // Maksimal 2MB
        ]);

        $user = $request->user(); 
        $file = $request->file('avatar');

        // 2. Buat nama file yang unik agar tidak bentrok di Supabase
        $fileName = 'avatar-' . $user->id . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();

        try {
            // 3. Upload langsung ke Supabase Storage menggunakan driver S3
            // File akan otomatis masuk ke bucket 'avatars' yang didefinisikan di .env
            $path = Storage::disk('s3')->putFileAs('', $file, $fileName, 'public');

            // 4. Hapus foto profil lama jika ada (Biar Supabase kamu gak penuh sampah)
            if ($user->avatar_url) {
                $oldFileName = basename($user->avatar_url);
                Storage::disk('s3')->delete($oldFileName);
            }

            // 5. Dapatkan URL Publik dari Supabase
            $publicUrl = Storage::disk('s3')->url($fileName);

            // 6. Simpan URL tersebut ke database kamu
            $user->update([
                'avatar_url' => $publicUrl
            ]);

            // 7. Kembalikan respon sukses ke Front-end (Web & Mobile)
            return response()->json([
                'status' => 'success',
                'message' => 'Foto profil berhasil diperbarui.',
                'avatar_url' => $publicUrl
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupload gambar: ' . $e->getMessage()
            ], 500);
        }
    }
}
