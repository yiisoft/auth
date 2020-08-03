<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

/**
 * HttpHeaderAuth supports HTTP authentication through HTTP Headers.
 *
 * The default implementation of HttpHeaderAuth uses the [[Yiisoft\Yii\Web\User\IdentityRepositoryInterface::findIdentityByToken()|findIdentityByToken()]]
 * method of the `user` application component and passes the value of the `X-Api-Key` header. This implementation is used
 * for authenticating API clients.
 */
class HttpHeader implements AuthInterface
{
    private const HEADER_NAME = 'X-Api-Key';
    private const PATTERN = '/(.*)/';

    /**
     * @var string the HTTP header name
     */
    protected string $headerName = self::HEADER_NAME;

    /**
     * @var string a pattern to use to extract the HTTP authentication value
     */
    protected string $pattern = self::PATTERN;

    /**
     * @var IdentityRepositoryInterface
     */
    protected IdentityRepositoryInterface $identityRepository;

    public function __construct(IdentityRepositoryInterface $identityRepository)
    {
        $this->identityRepository = $identityRepository;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $authToken = $this->getAuthToken($request);
        if ($authToken !== null) {
            return $this->identityRepository->findIdentityByToken($authToken, get_class($this));
        }

        return null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    public function setHeaderName(string $name): void
    {
        $this->headerName = $name;
    }

    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    protected function getAuthToken(ServerRequestInterface $request): ?string
    {
        $authHeaders = $request->getHeader($this->headerName);
        $authHeader = \reset($authHeaders);
        if (!empty($authHeader)) {
            if (preg_match($this->pattern, $authHeader, $matches)) {
                $authHeader = $matches[1];
            } else {
                return null;
            }
            return $authHeader;
        }
        return null;
    }
}
