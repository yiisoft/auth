<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Method;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\HttpBasic;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Method;

final class HttpBasicTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new HttpBasic($identityRepository))->authenticate(
            $this->createRequest(['PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'password'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals(
            [
                'findIdentityByToken' =>
                    [
                        'token' => 'user',
                        'type' => HttpBasic::class
                    ]
            ],
            $identityRepository->getCallParams()
        );
    }

    public function testIdentityNotFoundByToken(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authMethod = new HttpBasic($identityRepository);

        $this->assertNull(
            $authMethod->authenticate(
                $this->createRequest(['PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'password'])
            )
        );
    }

    public function testPassedOnlyPassword(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpBasic($identityRepository);

        $this->assertNull(
            $authMethod->authenticate(
                $this->createRequest(['PHP_AUTH_PW' => 'password'])
            )
        );
    }

    public function testSuccessfulAuthenticationWithAuthCallback(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authMethod = (new HttpBasic($identityRepository));
        $authMethod->setAuth(
            function (string $username, string $password): IdentityInterface {
                return $this->createIdentity($username . ':' . $password);
            }
        );

        $result = $authMethod->authenticate(
            $this->createRequest(['PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'password'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('user:password', $result->getId());
        $this->assertEmpty($identityRepository->getCallParams());
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response();
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpBasic($identityRepository);

        $this->assertEquals(
            'Basic realm="api"',
            $authMethod->challenge($response)->getHeaderLine('WWW-Authenticate')
        );
    }

    public function testCustomRealm(): void
    {
        $response = new Response();
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpBasic($identityRepository);
        $authMethod->setRealm('gateway');

        $this->assertEquals(
            'Basic realm="gateway"',
            $authMethod->challenge($response)->getHeaderLine('WWW-Authenticate')
        );
    }

    public function testSuccessfulAuthenticationWithHeaders(): void
    {
        $encodeFields = base64_encode('admin:pass');
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = (new HttpBasic($identityRepository));

        $authMethod->setAuth(
            function (string $username, string $password): IdentityInterface {
                return $this->createIdentity($username . ':' . $password);
            }
        );

        $result = $authMethod->authenticate(
            $this->createRequest([], ['Authorization' => 'Basic:' . $encodeFields])
        );

        $this->assertNotNull($result);
        $this->assertEquals('admin:pass', $result->getId());
    }

    public function testSuccessfulAuthenticationWithHeadersContainsOnlyUsername(): void
    {
        $encodeFields = base64_encode('username');
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new HttpBasic($identityRepository))->authenticate(
            $this->createRequest([], ['Authorization' => 'Basic:' . $encodeFields])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals(
            [
                'findIdentityByToken' =>
                    [
                        'token' => 'username',
                        'type' => HttpBasic::class
                    ]
            ],
            $identityRepository->getCallParams()
        );
    }

    public function testNotAuthenticationBecauseEmptyParams(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new HttpBasic($identityRepository))->authenticate(
            $this->createRequest()
        );

        $this->assertNull($result);
    }

    public function testSuccessfulAuthenticationWithRedirectParam(): void
    {
        $encodeFields = base64_encode('admin:pass');
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new HttpBasic($identityRepository))->authenticate(
            $this->createRequest(['REDIRECT_HTTP_AUTHORIZATION' => 'Basic:' . $encodeFields])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals(
            [
                'findIdentityByToken' =>
                    [
                        'token' => 'admin',
                        'type' => HttpBasic::class
                    ]
            ],
            $identityRepository->getCallParams()
        );
    }

    private function createIdentity(string $id = 'test-id'): IdentityInterface
    {
        return new FakeIdentity($id);
    }

    private function createRequest(array $serverParams = [], array $headers = []): ServerRequestInterface
    {
        return new ServerRequest(Method::GET, '/', $headers, null, '1.1', $serverParams);
    }
}
