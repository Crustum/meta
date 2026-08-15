<?php
declare(strict_types=1);

namespace Crustum\Meta\Rendering;

use InvalidArgumentException;
use function Cake\Core\h;

/**
 * Renders head tags into HTML strings.
 */
class TagRenderer
{
    /**
     * Render a title tag.
     *
     * @param string $title Title
     * @return string
     */
    public function title(string $title): string
    {
        return $this->element('title', h($title));
    }

    /**
     * Render a meta tag keyed by the given attribute ("name" or "property").
     *
     * @param string $attribute Meta attribute
     * @param string $key Meta key
     * @param string|float|int|bool $content Meta content
     * @return string
     */
    public function meta(string $attribute, string $key, string|int|float|bool $content): string
    {
        return $this->metaWithAttributes($attribute, $key, ['content' => (string)$content]);
    }

    /**
     * Render a meta tag keyed by the given attribute ("name" or "property") with arbitrary attributes.
     *
     * @param string $attribute Meta attribute
     * @param string $key Meta key
     * @param array<string, bool|float|int|string|null> $attributes Meta attributes
     * @return string
     */
    public function metaWithAttributes(string $attribute, string $key, array $attributes): string
    {
        return $this->voidElement(
            'meta',
            [$attribute => $key],
            $attributes,
        );
    }

    /**
     * Render a link tag.
     *
     * @param string $rel Link relation
     * @param string $href Link href
     * @return string
     */
    public function link(string $rel, string $href): string
    {
        return $this->voidElement(
            'link',
            ['rel' => $rel, 'href' => $href],
        );
    }

    /**
     * Render a link tag with arbitrary attributes.
     *
     * @param string $rel Link relation
     * @param array<string, bool|float|int|string|null> $attributes Link attributes
     * @return string
     */
    public function linkWithAttributes(string $rel, array $attributes): string
    {
        return $this->voidElement(
            'link',
            ['rel' => $rel],
            $attributes,
        );
    }

    /**
     * Render a JSON-LD script tag.
     *
     * @param array<string, mixed> $schema Schema data
     * @return string
     */
    public function jsonLd(array $schema): string
    {
        return $this->element(
            'script',
            json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR),
            ['type' => 'application/ld+json'],
        );
    }

    /**
     * Render a string of HTML attributes from a value map.
     *
     * @param array<string, bool|float|int|string|null> $attributes Attribute values
     * @return string
     */
    public function attributes(array $attributes): string
    {
        return $this->renderAttributes($attributes);
    }

    /**
     * Determine if the given string is a valid HTML attribute name.
     *
     * Attribute names are not HTML-escaped, so anything that could break out
     * of the attribute position is rejected. The "u" modifier makes invalid
     * UTF-8 fail the match.
     *
     * @param string $name Attribute name
     * @return bool
     */
    protected function isValidAttributeName(string $name): bool
    {
        return preg_match('/^[^\x00-\x20\x7F-\x9F"\'\/=>]+$/u', $name) === 1;
    }

    /**
     * Render HTML attributes from one or more value maps.
     *
     * @param array<int|string, bool|float|int|string|null> ...$attributeSets Attribute value sets
     * @return string
     * @throws \InvalidArgumentException When an attribute name is invalid or duplicated
     */
    protected function renderAttributes(array ...$attributeSets): string
    {
        $rendered = [];
        $seen = [];

        foreach ($attributeSets as $attributes) {
            foreach ($attributes as $name => $value) {
                if ($value === false) {
                    continue;
                }

                if (is_null($value)) {
                    continue;
                }

                $name = (string)$name;

                if (!$this->isValidAttributeName($name)) {
                    throw new InvalidArgumentException('Invalid HTML attribute name.');
                }

                $normalizedName = strtolower($name);

                if (isset($seen[$normalizedName])) {
                    throw new InvalidArgumentException(sprintf('Duplicate HTML attribute name [%s].', $name));
                }

                $seen[$normalizedName] = true;
                $rendered[] = $value === true
                    ? $name
                    : $name . '="' . h((string)$value) . '"';
            }
        }

        return implode(' ', $rendered);
    }

    /**
     * Render a void element tag with HTML attributes.
     *
     * @param string $name Element name
     * @param array<int|string, bool|float|int|string|null> ...$attributeSets Attribute value sets
     * @return string
     */
    protected function voidElement(string $name, array ...$attributeSets): string
    {
        return '<' . $name . $this->renderedAttributes(...$attributeSets) . '>';
    }

    /**
     * Render an element tag with content and HTML attributes.
     *
     * @param string $name Element name
     * @param string $content Element content
     * @param array<int|string, bool|float|int|string|null> ...$attributeSets Attribute value sets
     * @return string
     */
    protected function element(string $name, string $content, array ...$attributeSets): string
    {
        return '<' . $name . $this->renderedAttributes(...$attributeSets) . '>' . $content . '</' . $name . '>';
    }

    /**
     * Render HTML attributes prefixed by a single space when present.
     *
     * @param array<int|string, bool|float|int|string|null> ...$attributeSets Attribute value sets
     * @return string
     */
    protected function renderedAttributes(array ...$attributeSets): string
    {
        $attributes = $this->renderAttributes(...$attributeSets);

        return $attributes === '' ? '' : ' ' . $attributes;
    }
}
