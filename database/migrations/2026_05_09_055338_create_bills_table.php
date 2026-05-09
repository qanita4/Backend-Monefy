<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('bills', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // Langsung ke intinya
        $table->string('provider');       // Contoh: PLN, PDAM, Netflix
        $table->string('account_number'); // Nomor meteren / ID Pelanggan
        $table->decimal('amount', 15, 2); 
        $table->date('due_date')->index(); 
        $table->string('cycle');          // Monthly, Weekly, dll
        
        $table->enum('status', ['unpaid', 'paid'])->default('unpaid')->index();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
