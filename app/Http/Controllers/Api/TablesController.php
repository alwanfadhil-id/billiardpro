<?php

namespace App\Http\Controllers\Api;

use App\Models\Table;
use App\Http\Resources\TableResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class TablesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $tables = Table::all();
        return response()->json([
            'success' => true,
            'data' => TableResource::collection($tables)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tables,name',
            'hourly_rate' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance'
        ]);

        $table = Table::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Table created successfully.',
            'data' => new TableResource($table)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Table $table): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new TableResource($table)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Table $table): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100|unique:tables,name,' . $table->id,
            'hourly_rate' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:available,occupied,maintenance'
        ]);

        $table->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Table updated successfully.',
            'data' => new TableResource($table)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Table $table): JsonResponse
    {
        $table->delete();

        return response()->json([
            'success' => true,
            'message' => 'Table deleted successfully.'
        ]);
    }
}