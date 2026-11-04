<?php

namespace BB\Support;

/**
 * How a course is taught. Older courses may hold a value that isn't a case
 * here (some hold "unknown"), so Course::$format stays a
 * string rather than being cast to this enum.
 */
enum CourseFormat: string
{
    case Group = 'group';
    case Quiz = 'quiz';
    case OneOnOne = 'one-on-one';

    public function label(): string
    {
        return match ($this) {
            self::Group => 'Group class',
            self::Quiz => 'Online Quiz',
            self::OneOnOne => 'One-on-one',
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
