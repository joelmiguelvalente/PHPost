<?php

declare(strict_types=1);

/**
 * @package    PHPost/Extras
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 *
 * Polyfill para Uri\Rfc3986\Uri (PHP 8.5+)
 * Implementación básica usando parse_url() para PHP < 8.5
 */

namespace Uri\Rfc3986;

if (!extension_loaded('uri')) {
    if (!class_exists('Uri\Rfc3986\Uri')) {

        class Uri
        {
            private array $components;

            public function __construct(string $uri)
            {
                $parsed = parse_url($uri);
                if ($parsed === false) {
                    throw new \ValueError("URI malformada: {$uri}");
                }
                $this->components = $parsed;
            }

            public function getScheme(): ?string
            {
                return $this->components['scheme'] ?? null;
            }

            public function getHost(): ?string
            {
                return $this->components['host'] ?? null;
            }

            public function getPort(): ?int
            {
                return isset($this->components['port'])
                    ? (int) $this->components['port']
                    : null;
            }

            public function getPath(): string
            {
                return $this->components['path'] ?? '';
            }

            public function getQuery(): ?string
            {
                return $this->components['query'] ?? null;
            }

            public function getFragment(): ?string
            {
                return $this->components['fragment'] ?? null;
            }

            public function getUserInfo(): ?string
            {
                $user = $this->components['user'] ?? null;
                $pass = $this->components['pass'] ?? null;

                if ($user === null) return null;
                return $pass !== null ? "{$user}:{$pass}" : $user;
            }

            public function __toString(): string
            {
                $uri = '';

                if ($scheme = $this->getScheme()) {
                    $uri .= "{$scheme}://";
                }
                if ($userInfo = $this->getUserInfo()) {
                    $uri .= "{$userInfo}@";
                }
                if ($host = $this->getHost()) {
                    $uri .= $host;
                }
                if ($port = $this->getPort()) {
                    $uri .= ":{$port}";
                }

                $uri .= $this->getPath();

                if ($query = $this->getQuery()) {
                    $uri .= "?{$query}";
                }
                if ($fragment = $this->getFragment()) {
                    $uri .= "#{$fragment}";
                }

                return $uri;
            }
        }
    }
}
