<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product' => [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'active_ingredient' => $this->active_ingredient,
                'status' => $this->status,
                'image' => $this->image ? Storage::url($this->image) : null,
            ]
        ];
    }
}
