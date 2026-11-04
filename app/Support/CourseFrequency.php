<?php

namespace BB\Support;

/**
 * How often a course runs. Like CourseFormat, Course::$frequency stays a string
 * because older courses may hold a value that isn't a case here.
 */
enum CourseFrequency: string
{
    case SelfServe = 'self-serve';
    case Regular = 'regular';
    case AdHoc = 'ad-hoc';

    public function label(): string
    {
        return match ($this) {
            self::SelfServe => 'Self-serve',
            self::Regular => 'Regular',
            self::AdHoc => 'On-demand',
        };
    }

    /**
     * @return array<string, string> label by value, in display order
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
