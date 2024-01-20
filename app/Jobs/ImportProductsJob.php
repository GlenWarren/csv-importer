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

class ImportProductsJob implements ShouldQueue
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
        $handle = fopen(storage_path(Paths::PRICE), 'r');

        $first_iteration = true;
        $chunk_size = 1000;
        $data_chunk = [];
        $column_titles = [];

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
            foreach (Product::CSV_TITLES_TO_COLUMNS_MAP as $title => $column) {
                if (!isset($row_data[$title])) {
                    $this->fail("File is missing the following column title: '$title'");
                }
                $row_data_to_load[$column] = $row_data[$title];
            }
            $now = now();
            $row_data_to_load['created_at'] = $now;
            $row_data_to_load['updated_at'] = $now;
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
