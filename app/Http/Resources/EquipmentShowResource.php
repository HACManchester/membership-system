<?php

namespace BB\Http\Resources;

use BB\Repo\TrainingRecordRepository;
use BB\Support\PpeOptions;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The equipment detail payload for the (Inertia) show page. Markdown fields are
 * passed raw and rendered client-side.
 *
 * Inertia ships every prop to the browser whether or not it's rendered, so the
 * fields only some viewers may see are gated here to match the page's render
 * conditions: the access code and "instructions for use" go to trained members,
 * trainer instructions to trainers, induction instructions to members who have
 * started an induction, and admin notes to members who can edit the equipment.
 *
 * @mixin \BB\Entities\Equipment
 */
class EquipmentShowResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $request->user();

        $record = $user
            ? app(TrainingRecordRepository::class)->getUserForEquipment($this->resource, $user->id)
            : false;
        $trained = (bool) ($record && $record->trained);
        $isTrainer = $trained && (bool) $record->is_trainer;
        $canEdit = $user && $user->can('update', $this->resource);

        $photos = [];
        for ($i = 0; $i < $this->getNumPhotos(); $i++) {
            $photos[] = $this->getPhotoUrl($i);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'working' => (bool) $this->working,
            'dangerous' => (bool) $this->dangerous,
            'lone_working' => (bool) $this->lone_working,
            'permaloan' => (bool) $this->permaloan,
            'requires_induction' => (bool) $this->requires_induction,
            'accepting_inductions' => (bool) $this->accepting_inductions,
            'room_name' => optional($this->roomModel)->name,
            'location_detail' => $this->detail ?: null,
            'maintainer_group' => $this->maintainerGroup ? [
                'name' => $this->maintainerGroup->name,
                'url' => route('maintainer_groups.show', $this->maintainerGroup->slug, false),
            ] : null,
            'manufacturer_model' => $this->present()->manufacturerModel ?: null,
            'purchase_date' => $this->present()->purchaseDate,
            'usage_cost' => $this->hasUsageCharge() ? $this->present()->usageCost : null,
            'ppe' => collect($this->ppe)->map(function ($key) {
                return [
                    'key' => $key,
                    'label' => PpeOptions::getLabel($key) ?: $key,
                    'image' => asset('img/ppe/' . $key . '.jpg'),
                ];
            })->values(),
            'photos' => $photos,
            'description' => $this->description,
            'help_text' => $this->help_text,
            'docs' => $this->docs,
            'induction_instructions' => $this->when((bool) $record, $this->induction_instructions),
            'trained_instructions' => $this->when($trained, $this->trained_instructions),
            'trainer_instructions' => $this->when($isTrainer, $this->trainer_instructions),
            'access_code' => $this->when($trained && $this->access_code, $this->access_code),

            // Admin notes are only exposed to members who can edit the equipment
            // (the same people who can see them on the edit form).
            'admin_notes' => $this->when($canEdit, $this->admin_notes),
        ];
    }
}
