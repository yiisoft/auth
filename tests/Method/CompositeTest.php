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
use Yiisoft\Auth\Method\QueryParameter;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;

final class CompositeTest extends TestCase
{
    public function testIncorrectArguments(): void
    {
        $authenticationMethod = new Composite([
           'test'
        ]);

        $this->expectException(\RuntimeException::class);

        $authenticationMethod->authenticate(
            $this->createRequest([Header::AUTHORIZATION => 'Bearer api-key'])
        );
    }

    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());


        $authenticationMethod = new Composite([
            new QueryParameter($identityRepository),
            new HttpBearer($identityRepository)
        ]);

        $result = $authenticationMethod->authenticate(
            $this->createRequest([Header::AUTHORIZATION => 'Bearer api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());

        $result = $authenticationMethod->authenticate(
            $this->createRequest([], ['access-token' => 'access-token-value'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
    }

    public function testIdentityNotFoundByToken(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $nullIdentityRepository = new FakeIdentityRepository(null);

        $authenticationMethod = new Composite([
            new QueryParameter($nullIdentityRepository),
            new HttpBearer($identityRepository)
        ]);

        $result = $authenticationMethod->authenticate(
            $this->createRequest([], ['access-token' => 'access-token-value'])
        );

        $this->assertNull($result);


        $result = $authenticationMethod->authenticate(
            $this->createRequest([Header::AUTHORIZATION => 'Bearer api-key'])
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


        $authenticationMethod = (new Composite([
            new QueryParameter($identityRepository),
            new HttpBearer($identityRepository)
        ]));

        $this->assertEquals(
            'Authorization realm="api"',
            $authenticationMethod->challenge($response)->getHeaderLine(Header::WWW_AUTHENTICATE)
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
