<?php

namespace BB\Support;

class RoomOptions
{
    /**
     * Get all available room options as key-value pairs
     * 
     * @return array
     */
    public static function all()
    {
        return [
            'welding'      => 'Fabrication',
            'woodwork'     => 'Woodwork',
            'metalworking' => 'Metalwork',
            'visual-arts'  => 'Visual Arts', // Migrate to The Stage / Main Area
            'electronics'  => 'Electronics',
            'main-room'    => 'Main Area',
            'stage'        => 'The Stage',
            'bikespace'    => 'Bike Space',
            'snackspace'   => 'Snackspace',
            'bar'          => 'The Bar',
        ];
    }

    /**
     * Get the label for a specific room key
     * 
     * @param string $key
     * @return string|null
     */
    public static function getLabel($key)
    {
        $options = static::all();
        return $options[$key] ?? null;
    }

    /**
     * Get a formatted display name for a room
     * This handles cases where the room might not be in our predefined list
     * 
     * @param string $room
     * @return string
     */
    public static function getDisplayName($room)
    {
        if (empty($room)) {
            return '';
        }

        // First try to get the proper label
        $label = static::getLabel($room);
        if ($label) {
            return $label;
        }

        // Fallback: capitalize and replace dashes with spaces
        return ucwords(str_replace('-', ' ', $room));
    }
}
