<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Method;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\QueryParameter;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Method;

final class QueryParameterTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new QueryParameter($identityRepository))->authenticate(
            $this->createRequest(['access-token' => 'access-token-value'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals(
            [
                'findIdentityByToken' => [
                    'token' => 'access-token-value',
                    'type' => QueryParameter::class
                ]
            ],
            $identityRepository->getCallParams()
        );
    }

    public function testIdentityNotFoundByToken(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authenticationMethod = new QueryParameter($identityRepository);

        $this->assertNull(
            $authenticationMethod->authenticate(
                $this->createRequest(['access-token' => 'access-token-value'])
            )
        );
    }

    public function testInvalidTypeToken(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authenticationMethod = new QueryParameter($identityRepository);

        $this->assertNull(
            $authenticationMethod->authenticate(
                $this->createRequest(['access-token' => 100])
            )
        );

        $this->assertEmpty($identityRepository->getCallParams());
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response(400);
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authenticationMethod = new QueryParameter($identityRepository);

        $this->assertEquals(400, $authenticationMethod->challenge($response)->getStatusCode());
    }

    public function testCustomTokenParam(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authenticationMethod = (new QueryParameter($identityRepository))
            ->withParameterName('AuthToken');

        $result = $authenticationMethod->authenticate(
            $this->createRequest(['AuthToken' => 'AccessTokenValue'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
    }

    public function testImmutability(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $original = (new QueryParameter($identityRepository));
        $this->assertNotSame($original, $original->withParameterName('parameterName'));
    }


    private function createIdentity(): IdentityInterface
    {
        return new FakeIdentity('test-id');
    }

    private function createRequest(array $queryParams = []): ServerRequestInterface
    {
        return (new ServerRequest(Method::GET, '/'))
            ->withQueryParams($queryParams);
    }
}
