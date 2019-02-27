<?php

declare(strict_types=1);

namespace Paxal\Tests\Phproxy\PAC;

use Paxal\Phproxy\PAC\ResponseFactory;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    public function provideDataForTest(): array
    {
        return [
            [['google.com', '.google.com'], $this->getFileContents('1')],
        ];
    }

    /**
     * @param array  $translations The translations
     * @param string $expected     Expected contents
     *
     * @dataProvider provideDataForTest
     */
    public function test(array $translations, string $expected): void
    {
        $response = ResponseFactory::create('10.129.22.122:8001', $translations);
        self::assertSame(1, preg_match('@^HTTP/1.. 200 @s', $response));
        self::assertStringContainsString("\r\n\r\n", $response);
        [1 => $contents] = explode("\r\n\r\n", $response, 2);
        self::assertSame($expected, $contents);
    }

    private function getFileContents(string $name)
    {
        $contents = file_get_contents(__DIR__.'/Fixtures/'.$name.'.pac');
        if (false === $contents) {
            throw new \RuntimeException('Unable to read fixture data');
        }

        return $contents;
    }
}
