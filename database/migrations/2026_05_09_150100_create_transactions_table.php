<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
                $table->foreignId('to_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
                $table->string('title');
                $table->decimal('amount', 15, 2);
                $table->string('type');
                $table->string('category');
                $table->string('attachment')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('transaction_date');
                $table->foreignId('wishlist_id')->nullable()->constrained('wishlists')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
