<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BreweryMetaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total' => $this->resource['total'],
            'by_state' => $this->resource['by_state'],
            'by_type' => $this->resource['by_type'],
            'page' => $this->resource['page'] ?? 1,
            'per_page' => $this->resource['per_page'] ?? 50,
        ];
    }
}
