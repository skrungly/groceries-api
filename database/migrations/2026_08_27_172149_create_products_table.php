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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            // as with items, almost all fields are optional for ease of use
            $table->string('name', '128');
            $table->string('barcode', '128')->nullable()->default(null)->unique();
            $table->integer('shelf_life_opened')->nullable()->default(null);
            $table->integer('quantity')->default(1);
            $table->decimal('cost')->nullable()->default(null);
            $table->integer('net_weight')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
