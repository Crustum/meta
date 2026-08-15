<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Cake\Core\Configure;
use Crustum\Meta\Exceptions\InvalidSchema;
use Psr\Log\LoggerInterface;

/**
 * Validates JSON-LD schema data before rendering.
 */
class SchemaValidator
{
    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface|null $logger Logger for production warnings
     */
    public function __construct(protected ?LoggerInterface $logger = null)
    {
    }

    /**
     * Validate a schema array, throwing on invalid schemas outside production.
     *
     * @param array<string, mixed> $schema Schema data
     * @throws \Crustum\Meta\Exceptions\InvalidSchema When the schema is invalid
     * @return void
     */
    public function validate(array $schema): void
    {
        $message = match (true) {
            ($schema['@context'] ?? null) !== 'https://schema.org' => 'JSON-LD schemas must use the https://schema.org context.',
            ! isset($schema['@type']) || $schema['@type'] === '' => 'JSON-LD schemas must define an @type.',
            $this->containsEmptyValue($schema) => 'JSON-LD schemas may not contain null or empty string values.',
            default => null,
        };

        if (is_null($message)) {
            return;
        }

        if (! Configure::read('debug')) {
            $this->logger?->warning($message, ['schema' => $schema]);

            return;
        }

        throw new InvalidSchema($message);
    }

    /**
     * Determine if any nested value is null or an empty string.
     *
     * @param array<mixed> $values Values
     * @return bool
     */
    protected function containsEmptyValue(array $values): bool
    {
        foreach ($values as $value) {
            if ($value === null || $value === '') {
                return true;
            }

            if (is_array($value) && $this->containsEmptyValue($value)) {
                return true;
            }
        }

        return false;
    }
}
