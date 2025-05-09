<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // This ensures that a user can only have one entry for each product in their wishlist
            $table->unique(['user_id', 'product_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
