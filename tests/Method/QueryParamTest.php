<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Method;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\QueryParam;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Method;

final class QueryParamTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new QueryParam($identityRepository))->authenticate(
            $this->createRequest(['access-token' => 'access-token-value'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals(
            [
                'findIdentityByToken' => [
                    'token' => 'access-token-value',
                    'type' => QueryParam::class
                ]
            ],
            $identityRepository->getCallParams()
        );
    }

    public function testIdentityNotFoundByToken(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authMethod = new QueryParam($identityRepository);

        $this->assertNull(
            $authMethod->authenticate(
                $this->createRequest(['access-token' => 'access-token-value'])
            )
        );
    }

    public function testInvalidTypeToken(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authMethod = new QueryParam($identityRepository);

        $this->assertNull(
            $authMethod->authenticate(
                $this->createRequest(['access-token' => 100])
            )
        );

        $this->assertEmpty($identityRepository->getCallParams());
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response(400);
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new QueryParam($identityRepository);

        $this->assertEquals(400, $authMethod->challenge($response)->getStatusCode());
    }

    public function testCustomTokenParam(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new QueryParam($identityRepository);
        $authMethod->setTokenParam('AuthToken');

        $result = $authMethod->authenticate(
            $this->createRequest(['AuthToken' => 'AccessTokenValue'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
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
