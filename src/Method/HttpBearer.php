<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;

/**
 * HttpBearer supports the authentication method based on HTTP Bearer token.
 */
final class HttpBearer extends HttpHeader
{
    private const HEADER_NAME = 'Authorization';
    private const PATTERN = '/^Bearer\s+(.*?)$/';

    protected string $headerName = self::HEADER_NAME;
    protected string $pattern = self::PATTERN;
    /**
     * @var string the HTTP authentication realm
     */
    private string $realm = 'api';

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('WWW-Authenticate', "{$this->headerName} realm=\"{$this->realm}\"");
    }

    public function withRealm(string $realm): self
    {
        $new = clone $this;
        $new->realm = $realm;
        return $new;
    }
}
