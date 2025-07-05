<?php

namespace BB\Presenters;

use Carbon\Carbon;
use Laracasts\Presenter\Presenter;

/**
 * @property-read string $format
 * @property-read string $frequency
 */
class CoursePresenter extends Presenter
{
    public function format()
    {
        switch ($this->entity->format) {
            case 'group':
                return 'Group class';
            case 'quiz':
                return 'Online Quiz';
            case 'one-on-one':
                return 'One-on-one';
            case 'unknown':
                return 'Unknown';
            default:
                return $this->entity->format;
        }
    }
    public function frequency()
    {
        switch ($this->entity->frequency) {
            case 'self-serve':
                return 'Self-serve';
            case 'regular':
                return 'Regular';
            case 'ad-hoc':
                return 'On-demand';
            case 'unknown':
                return 'Unknown';
            default:
                return $this->entity->frequency;
        }
    }
}
