<?php

namespace App\Support;

use App\Models\InterviewAttempt;
use App\Models\InterviewQuestion;
use Illuminate\Support\Collection;
use Normalizer;

/** One interview, as the conversation it actually was. */
final class InterviewConversation
{
    /** @param array<int, ConversationTurn> $turns */
    private function __construct(
        public readonly array $turns,
        /** True when the stream was rebuilt from question/answer pairs because no transcript was stored. */
        public readonly bool $reconstructed,
    ) {
    }

    public static function for(InterviewAttempt $attempt): self
    {
        $attempt->loadMissing('questions.answer');

        $questions = $attempt->questions->sortBy('sequence')->values();
        $transcript = collect($attempt->transcript ?? [])
            ->filter(fn ($turn) => filled($turn['text'] ?? null))
            ->values();

        if ($transcript->isEmpty()) {
            return new self(self::fromQuestionsOnly($questions), reconstructed: true);
        }

        return new self(self::annotate($transcript, $questions), reconstructed: false);
    }

    public function isEmpty(): bool
    {
        return $this->turns === [];
    }

    /** How many planned questions were matched onto the transcript. */
    public function matchedQuestionCount(): int
    {
        return count(array_filter($this->turns, fn (ConversationTurn $t) => $t->question !== null));
    }

    /**
     * The transcript, with planned questions attached to the turns that asked them.
     *
     * @param  Collection<int, array<string, mixed>>  $transcript
     * @param  Collection<int, InterviewQuestion>  $questions
     * @return array<int, ConversationTurn>
     */
    private static function annotate(Collection $transcript, Collection $questions): array
    {
        // Questions are consumed in order and never re-matched.
        $pending = $questions->all();
        $turns = [];

        foreach ($transcript as $turn) {
            $isBot = strtoupper((string) ($turn['speaker'] ?? '')) !== 'HUMAN';
            $text = trim((string) ($turn['text'] ?? ''));

            $matched = null;

            if ($isBot && $pending !== []) {
                foreach ($pending as $i => $candidate) {
                    if (self::looksLikeSameQuestion($text, (string) $candidate->question_text)) {
                        $matched = $candidate;
                        unset($pending[$i]);

                        break;
                    }
                }
            }

            $turns[] = new ConversationTurn(
                isBot: $isBot,
                text: $text,
                timestamp: $turn['timestamp'] ?? null,
                question: $matched,
            );
        }

        return $turns;
    }

    /**
     * A conversation rebuilt from the planned pairs alone.
     *
     * @param  Collection<int, InterviewQuestion>  $questions
     * @return array<int, ConversationTurn>
     */
    private static function fromQuestionsOnly(Collection $questions): array
    {
        $turns = [];

        foreach ($questions as $question) {
            $turns[] = new ConversationTurn(
                isBot: true,
                text: (string) $question->question_text,
                timestamp: $question->asked_at?->toIso8601String(),
                question: $question,
            );

            $answer = $question->answer?->transcript ?: $question->answer?->answer_text;

            if (filled($answer)) {
                $turns[] = new ConversationTurn(
                    isBot: false,
                    text: (string) $answer,
                    timestamp: $question->answer?->started_at?->toIso8601String(),
                    question: null,
                );
            }
        }

        return $turns;
    }

    /** Whether a spoken turn is the planned question being asked. */
    private static function looksLikeSameQuestion(string $spoken, string $planned): bool
    {
        $a = self::normalize($spoken);
        $b = self::normalize($planned);

        // Below this, a "match" is a coincidence rather than a recognition.
        if (mb_strlen($b) < 12 || mb_strlen($a) < 12) {
            return false;
        }

        return str_contains($a, $b) || str_contains($b, $a);
    }

    /** Fold a turn down to something two renderings of it can be compared on. */
    private static function normalize(string $value): string
    {
        if (class_exists(Normalizer::class)) {
            $value = Normalizer::normalize($value, Normalizer::FORM_KC) ?: $value;
        }

        $value = mb_strtolower($value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
