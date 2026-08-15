<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * FAQ page schema.org object.
 */
#[SchemaType('FAQPage')]
class Faq extends SchemaObject
{
    /**
     * FAQ questions.
     *
     * @var array<int, array{'@type': string, name: string, acceptedAnswer: array{'@type': string, text: string}}>
     */
    protected array $questions = [];

    /**
     * Add a FAQ question and answer.
     *
     * @param string $name Question text
     * @param string $answer Answer text
     * @return static
     */
    public function question(string $name, string $answer): static
    {
        $this->questions[] = [
            '@type' => 'Question',
            'name' => $name,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];

        return $this->set('mainEntity', $this->questions);
    }

    /**
     * Add multiple FAQ questions keyed by question to answer.
     *
     * @param array<string, string> $questions Questions keyed to their answers
     * @return static
     */
    public function questions(array $questions): static
    {
        foreach ($questions as $name => $answer) {
            $this->question($name, $answer);
        }

        return $this;
    }
}
