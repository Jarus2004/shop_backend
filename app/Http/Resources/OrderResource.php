<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name ?? 'Customer',
            'user_email' => $this->user?->email,
            'total' => (float) $this->total,
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
