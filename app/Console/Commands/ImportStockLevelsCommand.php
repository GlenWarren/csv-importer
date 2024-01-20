<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use App\Jobs\UpdateStockLevelJob;
use App\Enums\Paths;

class ImportStockLevelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-stock-levels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and update product stock levels from CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!file_exists(storage_path(Paths::STOCK))) { 
            die('File not found: ' . Paths::STOCK);
        }

        $handle = fopen(storage_path(Paths::STOCK), 'r');

        $batch = Bus::batch([])->allowFailures()->dispatch();

        $jobs = [];

        /* Reading the file one row at a time */
        while (($row_string = fgets($handle)) !== false) {

            $row = explode("\t", $row_string);
            $jobs[] = new UpdateStockLevelJob($row);

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
}
