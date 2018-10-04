<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Translator;

class Translator implements TranslatorInterface
{
    private $translations = [];

    public function __construct(array $translations = [])
    {
        $this->translations = $translations;
    }

    public function translate(string $host): string
    {
        $translation = $this->translations[$host] ?? null;
        if (null !== $translation) {
            return $host;
        }

        [$domain, $port] = explode(':', $host, 2);

        return join(':', [$this->translate($domain), $port]);
    }
}
