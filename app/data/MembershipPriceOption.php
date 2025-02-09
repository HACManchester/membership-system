<?php namespace BB\Data;

/**
 * @property-read int $value_in_pence
 * @property-read string $title
 * @property-read string $description
 */
class MembershipPriceOption
{
    protected $title;
    protected $description;
    protected $value_in_pence;

    public function __construct($title, $description, $value_in_pence)
    {
        $this->title = $title;
        $this->description = $description;
        $this->value_in_pence = $value_in_pence;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        trigger_error("Undefined property: " . __CLASS__ . "::$name", E_USER_NOTICE);
        return null;
    }
}