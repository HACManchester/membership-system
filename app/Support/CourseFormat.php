<?php

namespace BB\Support;

/**
 * How a course is taught: Course::$format, which is cast to this enum.
 */
enum CourseFormat: string
{
    case Group = 'group';
    case Quiz = 'quiz';
    case OneOnOne = 'one-on-one';
    /** Some older courses hold this. It can't be chosen, so it's left out of options() */
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Group => 'Group class',
            self::Quiz => 'Online Quiz',
            self::OneOnOne => 'One-on-one',
            self::Unknown => 'Unknown',
        };
    }

    /**
     * @return list<self> the cases a course can be given
     */
    public static function choosable(): array
    {
        return [self::Group, self::Quiz, self::OneOnOne];
    }

    /**
     * @return array<string, string> label by value, in display order
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::choosable() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
