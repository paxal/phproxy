<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Command;

class OptionsHelper
{
    /**
     * @return array<string, string|array<string>>
     */
    public static function read(string $filename): array
    {
        $contents = @file_get_contents($filename);
        if (!is_string($contents)) {
            throw new \InvalidArgumentException('Unable to read '.$filename);
        }

        $json = @json_decode($contents, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('Unable to parse json in '.$filename);
        }

        if (!is_array($json)) {
            throw new \InvalidArgumentException('Invalid configuration file : json is valid, but an object was expected.');
        }

        return $json;
    }

    /**
     * @param array<string, string|array<string>> $options
     */
    public static function save(string $filename, array $options): void
    {
        @file_put_contents(
            $filename,
            json_encode($options, JSON_PRETTY_PRINT)
        );
    }
}
