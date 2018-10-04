<?php

declare(strict_types=1);

namespace Paxal\Tests\Phproxy\Translator;

use Paxal\Phproxy\Translator\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testRaw()
    {
        $translator = new Translator(['a.com' => 'b.net']);
        self::assertSame('c.org', $translator->translate('c.org'));
        self::assertSame('b.net', $translator->translate('b.net'));
        self::assertSame('b.net', $translator->translate('a.com'));
        self::assertSame('b.net:80', $translator->translate('a.com:80'));
    }

    public function testAllDomains()
    {
        $translator = new Translator(['.a.com' => '.b.net']);
        self::assertSame('c.org', $translator->translate('c.org'));
        self::assertSame('b.net', $translator->translate('b.net'));
        self::assertSame('a.com', $translator->translate('a.com'));
        self::assertSame('a.com:80', $translator->translate('a.com:80'));
        self::assertSame('www.b.net', $translator->translate('www.a.com'));
        self::assertSame('www.b.net:80', $translator->translate('www.a.com:80'));
    }

    public function testAllDomainsToIP()
    {
        $translator = new Translator(['.a.com' => '127.0.0.1']);
        self::assertSame('c.org', $translator->translate('c.org'));
        self::assertSame('b.net', $translator->translate('b.net'));
        self::assertSame('a.com', $translator->translate('a.com'));
        self::assertSame('a.com:80', $translator->translate('a.com:80'));
        self::assertSame('127.0.0.1', $translator->translate('www.a.com'));
        self::assertSame('127.0.0.1:80', $translator->translate('www.a.com:80'));
    }
}
