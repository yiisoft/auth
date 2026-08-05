<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Auth\AuthenticatorWithChallengeInterface;
use Yiisoft\Http\Header;

/**
 * Authentication method based on HTTP Bearer token.
 *
 * @see https://tools.ietf.org/html/rfc6750
 *
 * @psalm-suppress DeprecatedInterface
 */
final class HttpBearer extends HttpHeader implements AuthenticatorWithChallengeInterface
{
    protected string $headerName = Header::AUTHORIZATION;

    /**
     * @psalm-var non-empty-string
     */
    protected string $pattern = '/^Bearer\s+(.*?)$/';

    private string $realm = 'api';

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader(Header::WWW_AUTHENTICATE, "Bearer realm=\"{$this->realm}\"");
    }

    /**
     * @param string $realm The HTTP authentication realm.
     */
    public function withRealm(string $realm): self
    {
        $new = clone $this;
        $new->realm = $realm;
        return $new;
    }
}
