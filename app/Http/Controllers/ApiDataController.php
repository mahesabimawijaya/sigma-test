<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;


class ApiDataController extends Controller
{
    public function fetchApiData()
    {
        // Run the command to fetch data
        Artisan::call('app:fetch-api-data');

        // Fetch updated data
        $items = Item::latest()->get();

        // Return an Inertia response with updated data
        return Inertia::render('Items/Index', [
            'items' => $items,
            'message' => 'Data fetched successfully!'
        ]);
    }
}
