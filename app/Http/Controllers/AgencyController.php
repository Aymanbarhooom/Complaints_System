<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\GovernmentAgency;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    public function index() {
        return response()->json(GovernmentAgency::all());
    }

    public function store(Request $request) {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate(['name' => 'required|unique:government_agencies', 'description' => 'nullable|string']);
        $agency = GovernmentAgency::create($validated);

        return response()->json(['message' => 'Agency created', 'agency' => $agency]);
    }

    public function destroy($id) {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        GovernmentAgency::findOrFail($id)->delete();
        return response()->json(['message' => 'Agency deleted']);
    }
}
