<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Translator;

interface TranslatorInterface
{
    public function translate(string $host): string;
}
