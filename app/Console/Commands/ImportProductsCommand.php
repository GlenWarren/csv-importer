<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportProductsJob;
use App\Enums\Paths;

class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products data from CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!file_exists(storage_path(Paths::PRICE))) { 
            die('File not found: ' . Paths::PRICE);
        }

        ImportProductsJob::dispatch();
    }
}
