<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Enums\RobotsRule;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Robots meta tag builder.
 */
class Robots extends StringTagBuilder
{
    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'robots';
    }

    /**
     * Create a robots builder from directives.
     *
     * @param \Crustum\Meta\Enums\RobotsRule|array<mixed, mixed>|string $directives Robots directives
     * @return static
     */
    #[Override]
    public static function make(string|RobotsRule|array $directives): static
    {
        return new static(self::renderDirectives($directives));
    }

    /**
     * Create a builder from a route attribute value.
     *
     * @param string $key Route attribute key
     * @param mixed $value Route attribute value
     * @return self|null
     */
    #[Override]
    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== static::key()) {
            return null;
        }

        if (is_array($value)) {
            if ($value === []) {
                return new static();
            }

            $directives = static::renderDirectives($value);

            return $directives === '' ? null : new static($directives);
        }

        return is_string($value) || $value instanceof RobotsRule ? static::make($value) : null;
    }

    /**
     * Convert this builder into the HTML tags rendered by @head.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @param \Crustum\Meta\Rendering\TagRenderer $tags Tag renderer
     * @return array<int, string>
     */
    #[Override]
    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($robots = $this->render()) ? [$tags->meta('name', 'robots', $robots)] : [];
    }

    /**
     * Render directives into a comma-separated string.
     *
     * @param \Crustum\Meta\Enums\RobotsRule|array<mixed, mixed>|string $directives Robots directives
     * @return string
     */
    protected static function renderDirectives(string|RobotsRule|array $directives): string
    {
        if (is_string($directives)) {
            return $directives;
        }

        if ($directives instanceof RobotsRule) {
            return $directives->value;
        }

        return implode(', ', array_values(array_filter(array_map(
            fn(mixed $directive): ?string => match (true) {
                $directive instanceof RobotsRule => $directive->value,
                is_string($directive) => $directive,
                default => null,
            },
            $directives,
        ))));
    }
}
