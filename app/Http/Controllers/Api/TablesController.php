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
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $tables = Table::paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => TableResource::collection($tables),
            'links' => [
                'first' => $tables->url(1),
                'last' => $tables->url($tables->lastPage()),
                'prev' => $tables->previousPageUrl(),
                'next' => $tables->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $tables->currentPage(),
                'from' => $tables->firstItem(),
                'last_page' => $tables->lastPage(),
                'path' => $tables->path(),
                'per_page' => $tables->perPage(),
                'to' => $tables->lastItem(),
                'total' => $tables->total(),
            ],
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