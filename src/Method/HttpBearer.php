<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;

/**
 * HttpBearer supports the authentication method based on HTTP Bearer token.
 */
final class HttpBearer extends HttpHeader
{
    protected string $headerName = 'Authorization';
    protected string $pattern = '/^Bearer\s+(.*?)$/';
    private string $realm = 'api';

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('WWW-Authenticate', "{$this->headerName} realm=\"{$this->realm}\"");
    }

    /**
     * @param string $realm The HTTP authentication realm.
     * @return self
     */
    public function withRealm(string $realm): self
    {
        $new = clone $this;
        $new->realm = $realm;
        return $new;
    }
}
