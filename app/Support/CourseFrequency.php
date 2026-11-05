<?php

namespace BB\Support;

/**
 * How often a course runs: Course::$frequency, which is cast to this enum.
 */
enum CourseFrequency: string
{
    case SelfServe = 'self-serve';
    case Regular = 'regular';
    case AdHoc = 'ad-hoc';
    /** Some older courses hold this. It can't be chosen, so it's left out of options() */
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::SelfServe => 'Self-serve',
            self::Regular => 'Regular',
            self::AdHoc => 'On-demand',
            self::Unknown => 'Unknown',
        };
    }

    /**
     * @return list<self> the cases a course can be given
     */
    public static function choosable(): array
    {
        return [self::SelfServe, self::Regular, self::AdHoc];
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
