<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Translator;

class Translator implements TranslatorInterface
{
    /**
     * List of all translations.
     *
     * @var array<string, string>
     */
    private array $translations;

    /**
     * List of translations that translate a wildcard domain.
     *
     * @var array<string, string>
     */
    private array $suffixHostsTranslations;

    /**
     * @param array<string, string> $translations List of all translations
     */
    public function __construct(array $translations = [])
    {
        $this->translations = $translations;
        $this->suffixHostsTranslations = array_filter(
            $translations,
            static fn (string $hostname): bool => '.' === $hostname[0],
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Translate a host. Will *not* return null if no match is found, but returns something usable.
     *
     * @param string $host The host to translate
     *
     * @return string The translated host, maybe the host itself
     */
    public function translate(string $host): string
    {
        $translation = $this->translations[$host] ?? $this->tryWildcardDomain($host);
        if (null !== $translation) {
            return $translation;
        }

        // Translation might work for specific host/port, so let's keep this order.s
        if (str_contains($host, ':')) {
            [$domain, $port] = explode(':', $host, 2);

            return join(':', [$this->translate($domain), $port]);
        }

        return $host;
    }

    /**
     * Try to translate an host with wildcards domains.
     *
     * @param string $host The host name
     *
     * @return string|null null if no match, otherwise the translated domain
     */
    private function tryWildcardDomain(string $host): ?string
    {
        foreach ($this->suffixHostsTranslations as $fullDomainHost => $replacement) {
            // Replace if same suffix
            if (str_ends_with($host, $fullDomainHost)) {
                // Check if replacement is also a wildcard replacement
                if ('.' !== $replacement[0]) {
                    return $replacement;
                }

                return substr($host, 0, -strlen($fullDomainHost)).$replacement;
            }
        }

        return null;
    }
}
