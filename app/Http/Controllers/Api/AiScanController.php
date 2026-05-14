<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiScanController extends Controller
{
    public function __construct(protected GeminiService $gemini) {}

    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120', // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $file      = $request->file('image');
            $mimeType  = $file->getMimeType();
            $base64    = base64_encode(file_get_contents($file->getRealPath()));

            $result = $this->gemini->scanReceipt($base64, $mimeType);

            return response()->json([
                'success' => true,
                'message' => 'Struk berhasil diproses',
                'data'    => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses struk: ' . $e->getMessage()
            ], 500);
        }
    }
}