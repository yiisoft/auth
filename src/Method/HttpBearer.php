<?php

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;

/**
 * HttpBearerAuth supports the authentication method based on HTTP Bearer token.
 */
final class HttpBearer extends HttpHeader
{
    private const HEADER_NAME = 'Authorization';
    private const PATTERN = '/^Bearer\s+(.*?)$/';

    protected $headerName = self::HEADER_NAME;
    protected $pattern = self::PATTERN;
    /**
     * @var string the HTTP authentication realm
     */
    private $realm = 'api';

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('WWW-Authenticate', "{$this->headerName} realm=\"{$this->realm}\"");
    }

    public function setRealm(string $realm): void
    {
        $this->realm = $realm;
    }
}
