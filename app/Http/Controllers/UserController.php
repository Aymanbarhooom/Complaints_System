<?php 
namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(User::all());
    }

    public function addEmployee(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'firstName' => 'required|string',
            'lastName'  => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
            'agency_id' => 'required|exists:government_agencies,id',
            'cardId'    => 'nullable|string',
            'birthday'  => 'nullable|date',
        ]);

        $employee = User::create([
            'firstName' => $data['firstName'],
            'lastName'  => $data['lastName'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'employee',
            'agency_id' => $data['agency_id'],
            'cardId'    => $data['cardId'] ?? null,
            'birthday'  => $data['birthday'] ?? null,
        ]);

        return response()->json([
            'message' => 'Employee created successfully',
            'user'    => $employee,
            'status'  => 201
        ], 201);
    }

     public function remove(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
