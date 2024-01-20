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

        while (($row_string = fgets($handle)) !== false) {
            $row = explode("\t", $row_string);
            $batch->add(new UpdateStockLevelJob($row));
        }
        
        fclose($handle);
    }
}
