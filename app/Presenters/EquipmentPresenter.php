<?php namespace BB\Presenters;

use BB\Support\PpeOptions;
use Carbon\Carbon;
use Laracasts\Presenter\Presenter;

class EquipmentPresenter extends Presenter
{

    public function livesIn()
    {
        $string = optional($this->entity->roomModel)->name ?? '';
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