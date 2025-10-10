<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'table' => new TableResource($this->whenLoaded('table')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'duration_minutes' => $this->duration_minutes,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'cash_received' => $this->cash_received,
            'change_amount' => $this->change_amount,
            'status' => $this->status,
            'items' => TransactionItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}