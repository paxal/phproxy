<?php

declare(strict_types=1);

namespace Paxal\Phproxy\PAC;

use Symfony\Component\HttpFoundation\Response;

/**
 * PAC File Factory.
 */
class ResponseFactory
{
    /**
     * @param string   $proxyHost         The proxy host, as of client point of view
     * @param string[] $translatedDomains Translated hosts, wildcards begin with a dot "."
     *
     * @return string A full response
     */
    public static function create(string $proxyHost, array $translatedDomains): string
    {
        $template = <<<TEMPLATE
function FindProxyForURL(url, host) {
    // Redirect known traffic through proxy
%s

    // DEFAULT RULE: All traffic is direct.
    return "DIRECT";
}

TEMPLATE;

        $matches = self::buildMatches($proxyHost, $translatedDomains);

        $contents = sprintf($template, $matches);
        $response = Response::create(
            $contents,
            Response::HTTP_OK,
            [
                'content-type' => 'application/x-ns-proxy-autoconfig',
                'connection' => 'close',
                'content-length' => strlen($contents),
                'server' => 'phproxy',
            ]
        );

        return (string) $response;
    }

    /**
     * Build matches given hostnames.
     *
     * @param string   $proxyHost
     * @param string[] $domains
     *
     * @return string
     */
    private static function buildMatches(string $proxyHost, array $domains): string
    {
        $translatedDomainsAsMatch = array_map(
            function (string $domainSpec): string {
                return ('.' === $domainSpec[0] ? '*' : '').$domainSpec;
            },
            $domains
        );

        $addressMatchTemplate = <<<ADDRESS_MATCH
    if (shExpMatch(host, "%s"))
        return "PROXY %s";

ADDRESS_MATCH;

        $matches = array_map(
            function (string $domainAsMatch) use ($addressMatchTemplate, $proxyHost): string {
                return sprintf($addressMatchTemplate, $domainAsMatch, $proxyHost);
            },
            $translatedDomainsAsMatch
        );

        return join('', $matches);
    }
}
