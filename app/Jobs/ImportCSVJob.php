<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Enums\Paths;
use App\Models\Product;

class ImportCSVJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $handle = fopen(storage_path(Paths::STOCK), 'r');

        $stock_levels = [];
        while (($row_string = fgets($handle)) !== false) {

            $row = explode("\t", $row_string);

            if ($row[1] > 0) {
                $stock_levels[$row[0]] = $row[1];
            }
        }

        fclose($handle);

        $handle = fopen(storage_path(Paths::PRICE), 'r');
        $first_iteration = true;
        $chunk_size = 1000;

        /* Mapping CSV column titles that we want to extract data for, to corresponding DB columns */
        $titles_to_columns_map = [
            'ManProdNr' => 'sku',
            'ProdNr' => 'supplier_product_id',
            'TradePrice' => 'cost_price',
            'RRP' => 'rrp'
        ];

        $data_chunk = [];

        while (($row_string = fgets($handle)) !== false) {
            /* Fetch the column titles, only on the first iteration */
            if ($first_iteration) {
                $column_titles = explode("\t", $row_string);
                $first_iteration = false;
                continue;
            }

            /* Extracting all row data */
            $row = explode("\t", $row_string);
            $row_data = [];
            foreach ($column_titles as $index => $title) {
                $row_data[$title] = $row[$index];
            }

            /* Preparing data to store */
            $row_data_to_load = [];
            foreach ($titles_to_columns_map as $title => $column) {
                $row_data_to_load[$column] = $row_data[$title];

                $now = now();
                $row_data_to_load['created_at'] = $now;
                $row_data_to_load['updated_at'] = $now;

                /* Add stock level if set */
                if ($column === 'supplier_product_id') {
                    if (isset($stock_levels[$row_data[$title]])) {
                        $row_data_to_load['stock_level'] = $stock_levels[$row_data[$title]];
                    } else {
                        $row_data_to_load['stock_level'] = 0;
                    }
                };
            }
            array_push($data_chunk, $row_data_to_load);

            /* Store data in our DB in chunks */
            if (count($data_chunk) >= $chunk_size) {
                Product::insertOrIgnore($data_chunk);
                $data_chunk = [];
            }
        }
        
        fclose($handle);
    }
}
