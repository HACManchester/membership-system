<?php namespace BB\Presenters;

use Laracasts\Presenter\Presenter;

class ExpensePresenter extends Presenter
{

    public function category()
    {
        switch ($this->entity->category) {
            case 'consumables':
                return 'Consumables';
            case 'food':
                return 'Food';
            case 'equipment-repair':
                return 'Equipment Repair';
            case 'tools':
                return 'Tools';
            case 'infrastructure':
                return 'Infrastructure';
            case 'promotion':
                return 'Promotional Materials';
            default:
                return $this->entity->category;
        }
    }

    public function expense_date()
    {
        return $this->entity->expense_date->toFormattedDateString();
    }

    public function amount()
    {
        return '£' . number_format($this->entity->amount / 100, 2);
    }

    public function file()
    {
        $filePath = $this->entity->file;

        // Strip 'local' from the beginning, if this was saved before we moved the storage folder
        $filePath = preg_replace('/^local\//', '', $filePath);

        return asset('storage/' . $filePath);
    }

} 