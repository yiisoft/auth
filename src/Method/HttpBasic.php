<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

/**
 * HttpBasic supports the HTTP Basic authentication method.
 *
 * In case authentication does not work like expected, make sure your web server passes
 * username and password to `$request->getServerParams()['PHP_AUTH_USER']` and `$request->getServerParams()['PHP_AUTH_PW']`
 * variables. If you are using Apache with PHP-CGI, you might need to add this line to your `.htaccess` file:
 *
 * ```
 * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
 * ```
 */
final class HttpBasic implements AuthenticationMethodInterface
{
    /**
     * @var string The HTTP authentication realm.
     */
    private string $realm = 'api';

    /**
     * @var callable a PHP callable that will authenticate the user with the HTTP basic auth information.
     * The callable receives a username and a password as its parameters. It should return an identity object
     * that matches the username and password. Null should be returned if there is no such identity.
     * The callable will be called only if current user is not authenticated.
     *
     * If this property is not set, the username information will be considered as an access token
     * while the password information will be ignored. The {@see \Yiisoft\Auth\IdentityRepositoryInterface::findIdentityByToken()}
     * method will be called to authenticate an identity.
     */
    private $authenticationCallback;

    private IdentityRepositoryInterface $identityRepository;

    public function __construct(IdentityRepositoryInterface $identityRepository)
    {
        $this->identityRepository = $identityRepository;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        [$username, $password] = $this->getAuthenticationCredentials($request);


        if ($this->authenticationCallback && ($username !== null || $password !== null)) {
            return \call_user_func($this->authenticationCallback, $username, $password);
        }

        if ($username !== null) {
            return $this->identityRepository->findIdentityByToken($username, get_class($this));
        }

        return null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
    }

    public function withAuthenticationCallback(callable $authenticationCallback): self
    {
        $new = clone $this;
        $new->authenticationCallback = $authenticationCallback;
        return $new;
    }

    public function withRealm(string $realm): self
    {
        $new = clone $this;
        $new->realm = $realm;
        return $new;
    }

    /**
     * Obtains authentication credentials from request.
     *
     * @param ServerRequestInterface $request
     * @return array ['username', 'password'] array.
     */
    private function getAuthenticationCredentials(ServerRequestInterface $request): array
    {
        $username = $request->getServerParams()['PHP_AUTH_USER'] ?? null;
        $password = $request->getServerParams()['PHP_AUTH_PW'] ?? null;
        if ($username !== null || $password !== null) {
            return [$username, $password];
        }

        /*
         * Apache with php-cgi does not pass HTTP Basic authentication to PHP by default.
         * To make it work, add the following line to to your .htaccess file:
         *
         * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
         */
        $token = $this->getTokenFromHeaders($request);
        if ($token !== null && $this->isBasicToken($token)) {
            $credentials = $this->extractCredentialsFromHeader($token);
            if (\count($credentials) < 2) {
                return [$credentials[0], null];
            }

            return $credentials;
        }

        return [null, null];
    }

    private function getTokenFromHeaders(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if (!empty($header)) {
            return $header;
        }

        return $request->getServerParams()['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
    }

    private function extractCredentialsFromHeader(string $authToken): array
    {
        return array_map(
            fn ($value) => $value === '' ? null : $value,
            explode(':', base64_decode(mb_substr($authToken, 6)), 2)
        );
    }

    private function isBasicToken(string $token): bool
    {
        return strncasecmp($token, 'basic', 5) === 0;
    }
}
