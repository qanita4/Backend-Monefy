<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;

class GeminiService
{
    protected string $model = 'models/gemini-2.5-flash';

    public function scanReceipt(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        $prompt = <<<PROMPT
        Analisis foto dokumen keuangan ini. 
        Tentukan apakah ini 'expense' (struk belanja, tagihan) atau 'income' (slip gaji, invoice masuk).
        Ekstrak dalam format JSON:
        {
            "nama_toko": "nama merchant atau pengirim gaji",
            "tanggal": "YYYY-MM-DD",
            "total": integer_nominal,
            "type": "expense" atau "income",
            "kategori": "Gaji/Bonus/Makanan/Belanja/Lainnya",
            "catatan": "deskripsi singkat"
        }
        Kembalikan HANYA JSON.
        PROMPT;

        $blob = new \Gemini\Data\Blob(
            mimeType: \Gemini\Enums\MimeType::IMAGE_JPEG,
            data: $base64Image
        );

        $imagePart = new \Gemini\Data\Part(inlineData: $blob);
        $textPart  = new \Gemini\Data\Part(text: $prompt);

        $result = Gemini::generativeModel($this->model)
            ->generateContent([$blob, $prompt]);

        $raw     = $result->text();
        $clean   = trim(preg_replace('/```json|```/', '', $raw));
        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Gagal memproses respons AI: ' . $raw);
        }

        return $decoded;
    }

    public function chat(string $prompt): string
    {
        $result = Gemini::generativeModel($this->model)
            ->generateContent($prompt);

        return $result->text();
    }
}