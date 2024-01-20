<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Enums\Paths;
use App\Models\Product;
use App\Jobs\CreateProductJob;

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
        $column_titles = [];
        $jobs = [];

        $batch = Bus::batch([])->dispatch();

        while (($row_string = fgets($handle)) !== false) {
            /* Fetch the column titles, only on the first iteration */
            if ($first_iteration) {
                $column_titles = explode("\t", $row_string);
                $first_iteration = false;
                continue;
            }

            $new_product_data = $this->prepareData($row_string, $column_titles);
            $jobs[] = new CreateProductJob($new_product_data);

            /* Hydrating jobs 1000 at a time */
            if (count($jobs) >= 1000) {
                /* Job batch data stored in DB table job_batches */
                $batch->add($jobs);
                $jobs = [];
            }
        }

        /* Hydrate remaining jobs */
        $batch->add($jobs);
        
        fclose($handle);
    }

    private function prepareData(string $row_string, array $column_titles): array
    {
        /* Organising all row data */
        $row = explode("\t", $row_string);
        $row_data = [];
        foreach ($column_titles as $index => $title) {
            $row_data[$title] = $row[$index];
        }

        /* Extracting relevant data */
        $new_product_data = [];
        foreach (Product::CSV_TITLES_TO_COLUMNS_MAP as $title => $column) {
            if (!isset($row_data[$title])) {
                $this->fail("File is missing the following column title: '$title'");
            }
            $new_product_data[$column] = $row_data[$title];
        }
        $now = now();
        $new_product_data['created_at'] = $now;
        $new_product_data['updated_at'] = $now;

        return $new_product_data;
    }
}
