<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\GovernmentAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AgencyController extends Controller
{
 public function index()
    {
        $agencies = Cache::remember('government_agencies', 60 * 60, function () {
            return GovernmentAgency::all();
        });

        return response()->json([
            'data' => $agencies,
            'status' => 200
        ]);
    }

    /**
     * Store a new government agency (Admin only)
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $agency = GovernmentAgency::create($validated);

        // Clear cache after insert
        Cache::forget('government_agencies');

        return response()->json([
            'message' => 'Government agency created successfully',
            'agency' => $agency,
            'status' => 201
        ], 201);
    }

    /**
     * Delete a government agency (Admin only)
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $agency = GovernmentAgency::findOrFail($id);
        $agency->delete();

        // Clear cache after delete
        Cache::forget('government_agencies');

        return response()->json([
            'message' => 'Government agency deleted successfully',
            'status' => 200
        ]);
    }
}