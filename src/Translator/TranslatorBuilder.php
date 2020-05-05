<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Translator;

class TranslatorBuilder
{
    /**
     * @var array<string, string>
     */
    private $translations = [];

    protected function __construct()
    {
    }

    /**
     * Build the translator.
     */
    public function build(): TranslatorInterface
    {
        return new Translator($this->translations);
    }

    public static function create(): self
    {
        return new self();
    }

    public function set(string $name, string $value): self
    {
        $this->translations[$name] = $value;

        return $this;
    }

    /**
     * @param iterable<string, string> $collection
     *
     * @return TranslatorBuilder
     */
    public function add(iterable $collection): self
    {
        foreach ($collection as $name => $value) {
            $this->set($name, $value);
        }

        return $this;
    }

    /**
     * Retrieve list of translated domains / wildcards.
     *
     * @return string[]
     */
    public function getTranslatedDomains(): array
    {
        return array_keys($this->translations);
    }
}
