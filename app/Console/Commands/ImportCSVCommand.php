<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportCSVJob;

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
        ImportCSVJob::dispatch();
    }
}
