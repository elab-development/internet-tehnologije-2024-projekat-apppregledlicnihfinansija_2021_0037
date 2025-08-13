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
            'id'          => $this->id,
            'type'        => $this->type,                       // 'income' | 'expense'
            'amount'      => (float) $this->amount,
            'date'        => optional($this->date)->toDateString(), // 'YYYY-MM-DD'
            'description' => $this->description,

            // Uključujemo kategoriju samo ako je eager-load-ovana (->with('category'))
            'category'    => $this->whenLoaded('category', function () {
                return $this->category ? [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ] : null;
            }),

            'created_at'  => optional($this->created_at)->toISOString(),
            'updated_at'  => optional($this->updated_at)->toISOString(),
        ];
    }
}

