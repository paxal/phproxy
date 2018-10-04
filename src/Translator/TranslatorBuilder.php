<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Translator;

class TranslatorBuilder
{
    private $translations = [];

    protected function __construct()
    {
    }

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

    public function add(iterable $collection): self
    {
        foreach ($collection as $name => $value) {
            $this->translations[(string) $name] = (string) $value;
        }

        return $this;
    }
}
