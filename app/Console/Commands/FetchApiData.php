<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use Illuminate\Support\Facades\Http;

class FetchApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-api-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get('https://randomuser.me/api/?results=10');
        foreach ($response->json()['results'] as $user) {
            Item::create([
                'name' => $user['name']['first'] . ' ' . $user['name']['last'],
                'category' => $user['gender'],
                'data_date' => now(),
            ]);
        }
        $this->info('Data fetched successfully!');
    }
}
