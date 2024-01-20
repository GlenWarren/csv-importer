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
            $table->id();
            $table->string('sku')->unique('sku'); // (ManProdNr)
            $table->unsignedInteger('supplier_product_id')->unique('supplier_product_id'); // (ProdNr)
            $table->decimal('cost_price', 10, 2)->default(0.00); // (TradePrice)
            $table->decimal('rrp', 10, 2)->default(0.00); // (RRP)
            $table->unsignedInteger('stock_level')->default(0); // (Found in Stock File, under supplier_product_id/ProdNr)
            $table->timestamps();
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
