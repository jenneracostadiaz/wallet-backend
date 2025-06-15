<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $name
 * @property $type
 * @property $icon
 * @property $parent_id
 * @property $user_id
 * @property $order
 */
class SubcategoryResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'icon' => $this->icon,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'order' => $this->order,
        ];
    }
}
