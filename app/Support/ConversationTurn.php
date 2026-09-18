<?php

namespace App\Support;

use App\Models\InterviewQuestion;

/** One utterance in an interview, ready to render as a chat bubble. */
final class ConversationTurn
{
    public function __construct(
        public readonly bool $isBot,
        public readonly string $text,
        public readonly ?string $timestamp = null,
        public readonly ?InterviewQuestion $question = null,
    ) {
    }

    /** What the bot was probing for, when this turn was a planned question. */
    public function intent(): ?string
    {
        if (! $this->question) {
            return null;
        }

        return $this->question->metadata['intent']
            ?? $this->question->type?->value;
    }

    public function skillArea(): ?string
    {
        return $this->question?->skill_area;
    }
}
