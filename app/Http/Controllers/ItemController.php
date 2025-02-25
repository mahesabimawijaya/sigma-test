<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Inertia\Inertia;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::query();
        if ($request->filled('search')) {
            $searchTerm = "%{$request->search}%";

            $query->where(function ($query) use ($searchTerm) {
                $query->where('name', 'ILIKE', $searchTerm)
                    ->orWhere('category', 'ILIKE', $searchTerm)
                    ->orWhere('data_date', 'ILIKE', $searchTerm);
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return Inertia::render('Items/Index', [
            'items' => $query->orderBy('updated_at', 'desc')->paginate(5),
            'filters' => $request->only(['search', 'category']),
        ]);
    }


    public function genderStats(Request $request)
    {
        $query = Item::selectRaw('category, COUNT(*) as total')->groupBy('category');

        // Apply date filter only if start_date and end_date are provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('data_date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->get());
    }

    public function aggregateStats(Request $request)
    {
        $query = Item::selectRaw('DATE(data_date) as date, COUNT(*) as total')->groupBy('date')->orderBy('date', 'ASC');

        // Apply date filter only if start_date and end_date are provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('data_date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->get());
    }

    public function genderAggregStats(Request $request)
    {
        $query = Item::selectRaw(
            "DATE(data_date) as date, 
        SUM(CASE WHEN category = 'male' THEN 1 ELSE 0 END) as male, 
        SUM(CASE WHEN category = 'female' THEN 1 ELSE 0 END) as female"
        )
            ->groupBy('date')
            ->orderBy('date', 'ASC');

        // Apply date filter only if start_date and end_date are provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('data_date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->get());
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
        ]);

        Item::create([
            'name' => $request->name,
            'category' => $request->category,
            'data_date' => now(),
        ]);


        return redirect()->route('items.index')->with('success', 'Item added successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
        ]);

        $item->update($request->all());

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }
}
