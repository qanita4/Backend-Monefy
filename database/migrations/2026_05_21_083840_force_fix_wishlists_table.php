<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            // Kita TIDAK menyentuh atau menghapus kolom 'name' lagi
            
            // Cukup tambahkan kolom nominal target jika belum ada
            if (!Schema::hasColumn('wishlists', 'target_amount')) {
                $table->decimal('target_amount', 15, 2)->default(0)->after('name');
            }
            
            // Tambahkan kolom notes jika belum ada
            if (!Schema::hasColumn('wishlists', 'notes')) {
                $table->text('notes')->nullable()->after('target_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn(['target_amount', 'notes']);
        });
    }
};