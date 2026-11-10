<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name ?? 'Unknown Product',
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
            'subtotal' => round((float) $this->price * (int) $this->quantity, 2),
        ];
    }
}
