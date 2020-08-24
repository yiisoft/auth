<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Method;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\Composite;
use Yiisoft\Auth\Method\HttpBearer;
use Yiisoft\Auth\Method\QueryParam;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Method;

final class CompositeTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());


        $authMethod = new Composite([
            new QueryParam($identityRepository),
            new HttpBearer($identityRepository)
        ]);

        $result = $authMethod->authenticate(
            $this->createRequest(['Authorization' => 'Bearer api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());

        $result = $authMethod->authenticate(
            $this->createRequest([], ['access-token' => 'access-token-value'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
    }

    public function testIdentityNotFoundByToken(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $nullIdentityRepository = new FakeIdentityRepository(null);

        $authMethod = new Composite([
            new QueryParam($nullIdentityRepository),
            new HttpBearer($identityRepository)
        ]);

        $result = $authMethod->authenticate(
            $this->createRequest([], ['access-token' => 'access-token-value'])
        );

        $this->assertNull($result);


        $result = $authMethod->authenticate(
            $this->createRequest(['Authorization' => 'Bearer api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
    }

    public function testEmptyAuthMethods(): void
    {
        $result = (new Composite([]))->authenticate(
            $this->createRequest()
        );

        $this->assertNull($result);
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response(400);
        $identityRepository = new FakeIdentityRepository($this->createIdentity());


        $authMethod = (new Composite([
            new QueryParam($identityRepository),
            new HttpBearer($identityRepository)
        ]));

        $this->assertEquals(
            'Authorization realm="api"',
            $authMethod->challenge($response)->getHeaderLine('WWW-Authenticate')
        );
    }

    private function createIdentity(): IdentityInterface
    {
        return new FakeIdentity('test-id');
    }

    private function createRequest(array $headers = [], array $queryParams = []): ServerRequestInterface
    {
        return (new ServerRequest(Method::GET, '/', $headers))
            ->withQueryParams($queryParams);
    }
}
