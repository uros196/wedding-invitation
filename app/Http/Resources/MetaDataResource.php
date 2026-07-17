<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\MetaData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MetaData
 */
class MetaDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
