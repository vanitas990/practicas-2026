<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Vehicle::with('customer')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plate' => 'required|unique:vehicles,plate',
            'brand' => 'required',
            'model' => 'required',
            'year' => 'required|integer',
            'color' => 'nullable',
            'mileage' => 'nullable|integer',
            'tire_size' => 'nullable|string',
        ]);

        // 🔑 CLAVE: valor por defecto SIN tocar la BD
        $validated['tire_size'] = $validated['tire_size'] ?? 'N/A';

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'data' => $vehicle
        ], 201);
    }
}
