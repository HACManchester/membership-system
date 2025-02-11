<?php

namespace BB\Support;

class PpeOptions
{
    /**
     * Get all available PPE options as key-value pairs
     * 
     * @return array
     */
    public static function all()
    {
        return [
            'ear-protection'      => 'Ear protection',
            'eye-protection'      => 'Eye protection',
            'face-mask'           => 'Face mask',
            'face-guard'          => 'Full face guard',
            'gloves'              => 'Gloves',
            'protective-clothing' => 'Protective clothing',
            'welding-mask'        => 'Welding mask'
        ];
    }

    /**
     * Get the label for a specific PPE key
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
     * Convert an array of PPE keys to their labels
     * 
     * @param array $keys
     * @return array
     */
    public static function getLabels(array $keys)
    {
        return array_map(function($key) {
            return static::getLabel($key) ?? $key;
        }, $keys);
    }

    /**
     * Get a text representation of PPE items
     * 
     * @param array $keys
     * @return string
     */
    public static function asText(array $keys)
    {
        $labels = static::getLabels($keys);
        return implode(', ', $labels);
    }
}