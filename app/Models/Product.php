<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'supplier_product_id',
        'cost_price',
        'rrp',
        'stock_level'
    ];

    const CSV_TITLES_TO_COLUMNS_MAP = [
        'ManProdNr' => 'sku',
        'ProdNr' => 'supplier_product_id',
        'TradePrice' => 'cost_price',
        'RRP' => 'rrp'
    ];
}
