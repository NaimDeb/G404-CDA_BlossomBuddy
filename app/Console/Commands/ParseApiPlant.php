<?php

namespace App\Console\Commands;

use App\Interfaces\PlantServiceInterface;
use Illuminate\Console\Command;

class ParseApiPlant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-api-plant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    protected PlantServiceInterface $plantService;

    public function __construct(PlantServiceInterface $plantService)
    {
        parent::__construct();
        $this->plantService = $plantService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching plants...');
        $this->plantService->fetchAndStorePlants();
        $this->info('Plants fetched and stored successfully.');
    }
}
