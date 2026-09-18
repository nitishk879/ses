<?php

namespace App\Enums;

/** What a project requirement asserts about a candidate. */
enum RequirementKind: string
{
    case SKILL = 'skill';
    case EXPERIENCE = 'experience';
    case LANGUAGE = 'language';
    case LOCATION = 'location';
    case BUDGET = 'budget';

    /** Translated label for the requirement list. */
    public function label(): string
    {
        return __("interview.requirement.kind.{$this->value}");
    }

    /** Whether a recruiter may add one of these by hand. */
    public function isManuallyAddable(): bool
    {
        return in_array($this, [self::SKILL, self::EXPERIENCE, self::LANGUAGE], true);
    }
}
