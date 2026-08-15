<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Cake\Http\ServerRequest;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Canonical link tag builder.
 *
 * @phpstan-consistent-constructor
 */
class Canonical extends TagBuilder
{
    /**
     * Constructor.
     *
     * @param string $mode Either "url" for an explicit URL or "auto" to resolve from the request
     * @param string|null $url Explicit canonical URL
     * @param bool|null $forceHttps Force the https scheme
     * @param bool|null $trailingSlash Add a trailing slash
     */
    public function __construct(
        protected string $mode,
        protected ?string $url = null,
        protected ?bool $forceHttps = null,
        protected ?bool $trailingSlash = null,
    ) {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'canonical';
    }

    /**
     * Create a canonical builder. Use a null $url to resolve the canonical
     * URL from the current request.
     *
     * @param string|null $url Canonical URL
     * @param bool|null $forceHttps Force the https scheme
     * @param bool|null $trailingSlash Add a trailing slash
     * @return self
     */
    public static function make(?string $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): self
    {
        return new self(is_null($url) ? 'auto' : 'url', $url, $forceHttps, $trailingSlash);
    }

    /**
     * Create a builder from a route attribute value.
     *
     * @param string $key Route attribute key
     * @param mixed $value Route attribute value
     * @return self|null
     */
    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== 'canonical') {
            return null;
        }

        return match (true) {
            is_array($value) => self::fromRouteAttributeArray($value),
            is_string($value) => self::make($value),
            $value === true || is_null($value) => self::make(),
            default => null,
        };
    }

    /**
     * Merge this builder over the given base builder.
     *
     * @param \Crustum\Meta\Tags\TagBuilder|null $base Base builder
     * @return static
     */
    public function overlayOn(?TagBuilder $base): static
    {
        if (!$base instanceof self) {
            return $this;
        }

        return new static(
            $this->mode,
            $this->url ?? $base->url,
            $this->forceHttps ?? $base->forceHttps,
            $this->trailingSlash ?? $base->trailingSlash,
        );
    }

    /**
     * Canonical builders always carry a resolution mode, so they are never empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * Render the canonical URL.
     *
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @return string|null
     */
    public function render(?ServerRequest $request): ?string
    {
        $url = match ($this->mode) {
            'auto' => $request instanceof ServerRequest ? $this->currentUrl($request) : null,
            'url' => $this->url,
            default => null,
        };

        if (is_null($url)) {
            return null;
        }

        return $this->normalizeUrl($url, $request, $this->forceHttps ?? true, $this->trailingSlash ?? false);
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return string|null
     */
    public function toHeadArray(ResolvedHead $head): ?string
    {
        return $this->render($head->request());
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
        return ($canonical = $this->render($head->request())) ? [$tags->link('canonical', $canonical)] : [];
    }

    /**
     * Create a builder from an array of route attribute values.
     *
     * @param array<mixed, mixed> $attributes Route attribute values
     * @return self|null
     */
    private static function fromRouteAttributeArray(array $attributes): ?self
    {
        if (array_key_exists('none', $attributes) || ($attributes['value'] ?? null) === false) {
            return null;
        }

        $value = self::routeAttributeUrl($attributes);

        return is_string($value) || is_null($value)
            ? self::make(
                $value,
                forceHttps: self::bool($attributes['forceHttps'] ?? null),
                trailingSlash: self::bool($attributes['trailingSlash'] ?? null),
            )
            : null;
    }

    /**
     * Resolve the URL value from route attributes.
     *
     * @param array<mixed, mixed> $attributes Route attribute values
     * @return mixed
     */
    private static function routeAttributeUrl(array $attributes): mixed
    {
        $value = $attributes['value'] ?? null;

        return $value === true || self::bool($attributes['auto'] ?? null) === true
            ? null
            : $value;
    }

    /**
     * Normalize a canonical URL, forcing https and applying trailing slash rules.
     *
     * @param string $url Canonical URL
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @param bool $forceHttps Force the https scheme
     * @param bool $trailingSlash Add a trailing slash
     * @return string
     */
    protected function normalizeUrl(string $url, ?ServerRequest $request, bool $forceHttps, bool $trailingSlash): string
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = rtrim($this->schemeAndHttpHost($request) ?? '', '/') . '/' . ltrim($url, '/');
        }

        if ($forceHttps) {
            $url = preg_replace('/^http:\/\//', 'https://', $url) ?? $url;
        }

        if ($trailingSlash) {
            return rtrim($url, '/') . '/';
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (in_array($path, [null, false, '', '/'], true)) {
            return rtrim($url, '/') . '/';
        }

        return rtrim($url, '/');
    }

    /**
     * The current request URL without a query string.
     *
     * @param \Cake\Http\ServerRequest $request Current request
     * @return string
     */
    protected function currentUrl(ServerRequest $request): string
    {
        $uri = $request->getUri();

        return rtrim((string)$this->schemeAndHttpHost($request), '/') . $uri->getPath();
    }

    /**
     * The current request scheme and host.
     *
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @return string|null
     */
    protected function schemeAndHttpHost(?ServerRequest $request): ?string
    {
        if (!$request instanceof ServerRequest) {
            return null;
        }

        $uri = $request->getUri();

        return $uri->getScheme() . '://' . $uri->getHost();
    }
}
