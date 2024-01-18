<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportCSVJob;
use App\Enums\Paths;

class ImportCSVCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!file_exists(storage_path(Paths::PRICE))) { 
            die('File not found: ' . Paths::PRICE);
        } else if (!file_exists(storage_path(Paths::STOCK))) {
            die('File not found: ' . Paths::STOCK);
        }
        
        ImportCSVJob::dispatch();
    }
}
