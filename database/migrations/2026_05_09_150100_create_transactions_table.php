<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('wallet_id');
                $table->unsignedBigInteger('to_wallet_id')->nullable();
                $table->string('title');
                $table->decimal('amount', 15, 2);
                $table->string('type');
                $table->string('category');
                $table->string('attachment')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('transaction_date');
                $table->unsignedBigInteger('wishlist_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
