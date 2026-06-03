<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->softDeletes(); // <-- Ini untuk menambah kolom deleted_at
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropSoftDeletes(); // <-- Ini untuk menghapus kolom jika migrasi di-rollback
        });
    }
};