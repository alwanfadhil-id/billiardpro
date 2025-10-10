<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionItemResource;
use App\Http\Resources\TableResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $transactions = Transaction::with(['table', 'user', 'items.product'])->get();
        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'user_id' => 'required|exists:users,id',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date',
            'duration_minutes' => 'sometimes|integer|min:0',
            'total' => 'sometimes|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,debit,credit,other',
            'cash_received' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:ongoing,completed,cancelled',
        ]);

        $transaction = Transaction::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully.',
            'data' => new TransactionResource($transaction)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['table', 'user', 'items.product']);
        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'sometimes|required|exists:tables,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'started_at' => 'sometimes|required|date',
            'ended_at' => 'nullable|date',
            'duration_minutes' => 'sometimes|integer|min:0',
            'total' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|required|in:cash,qris,debit,credit,other',
            'cash_received' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'status' => 'sometimes|required|in:ongoing,completed,cancelled',
        ]);

        $transaction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully.',
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully.'
        ]);
    }
}