<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Translator;

class Translator implements TranslatorInterface
{
    /**
     * @var string[]
     */
    private $translations = [];

    /**
     * @var string[]
     */
    private $fullDomainHosts = [];

    /**
     * @var string[]
     */
    public function __construct(array $translations = [])
    {
        $this->translations = $translations;
        $this->fullDomainHosts = array_filter(
            $translations,
            function (string $hostname) {
                return '.' === $hostname[0];
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    public function translate(string $host): string
    {
        $translation = $this->translations[$host] ?? $this->tryFullDomain($host);
        if (null !== $translation) {
            return $translation;
        }

        if (false !== strpos($host, ':')) {
            [$domain, $port] = explode(':', $host, 2);

            return join(':', [$this->translate($domain), $port]);
        }

        return $host;
    }

    private function tryFullDomain(string $host): ?string
    {
        foreach ($this->fullDomainHosts as $fullDomainHost => $replacement) {
            if (substr($host, -strlen($fullDomainHost)) === $fullDomainHost) {
                if (false !== inet_pton($replacement)) {
                    return $replacement;
                }

                return substr($host, 0, -strlen($fullDomainHost)).$replacement;
            }
        }

        return null;
    }
}
