<?php namespace BB\Presenters;

use BB\Support\PpeOptions;
use BB\Support\RoomOptions;
use Carbon\Carbon;
use Laracasts\Presenter\Presenter;
use Michelf\Markdown;

class EquipmentPresenter extends Presenter
{

    public function livesIn()
    {
        $string = RoomOptions::getDisplayName($this->entity->room);
        if ($this->entity->detail) {
            $string .= ' (' . $this->entity->detail . ')';
        }
        return $string;
    }

    public function manufacturerModel()
    {
        $string = $this->entity->manufacturer;
        if ($this->entity->model_number) {
            $string .= ' (' . $this->entity->model_number . ')';
        }
        return $string;
    }

    public function description()
    {
        return Markdown::defaultTransform($this->entity->description);
    }

    public function induction_instructions()
    {
        return Markdown::defaultTransform($this->entity->induction_instructions);
    }

    public function trained_instructions()
    {
        return Markdown::defaultTransform($this->entity->trained_instructions);
    }

    public function trainer_instructions()
    {
        return Markdown::defaultTransform($this->entity->trainer_instructions);
    }

    public function help_text()
    {
        return Markdown::defaultTransform($this->entity->help_text);
    }

    public function purchaseDate()
    {
        if ( ! $this->entity->obtained_at) {
            return null;
        }
        return $this->entity->obtained_at->toFormattedDateString();
    }

    public function accessFee()
    {
        return '£' . number_format($this->entity->access_fee, 2);
    }

    public function usageCost()
    {
        if ($this->entity->usage_cost) {
            return '£' . number_format($this->entity->usage_cost, 2) . ' per ' . $this->entity->usage_cost_per;
        }
    }

    public function ppe()
    {
        $ppeHtml = '';
        foreach ($this->entity->ppe as $ppe) {
            $ppeHtml .= '<img src="/img/ppe/' . $ppe . '.jpg" height="140" class="ppe-image">';
        }
        $ppeHtml .= '<br /><br />';
        return $ppeHtml;
    }

    /**
     * Get PPE as a text representation
     * 
     * @return string
     */
    public function ppeText()
    {
        return PpeOptions::asText($this->entity->ppe);
    }

    /**
     * Get PPE labels as an array
     * 
     * @return array
     */
    public function ppeLabels()
    {
        return PpeOptions::getLabels($this->entity->ppe);
    }


} 