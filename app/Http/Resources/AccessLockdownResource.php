<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \BB\Entities\AccessLockdown
 */
class AccessLockdownResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'roles' => $this->roles,
            // Names only - Inertia ships every prop to the browser, so don't send
            // whole member records to render a byline.
            'started_by' => optional($this->startedBy)->name,
            'started_at' => optional($this->created_at)->toIso8601String(),
            'lifted_by' => optional($this->liftedBy)->name,
            'lifted_at' => optional($this->lifted_at)->toIso8601String(),
        ];
    }
}
