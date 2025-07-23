<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\Induction
 */
class InductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'key' => $this->key,
            'trained' => $this->trained,
            'is_trainer' => $this->is_trainer,
        ];
    }
}