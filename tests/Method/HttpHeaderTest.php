<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Method;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\HttpHeader;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Method;

final class HttpHeaderTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new HttpHeader($identityRepository))->authenticate(
            $this->createRequest(['X-Api-Key' => 'api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
    }

    public function testIdentityNotFoundByToken(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authMethod = new HttpHeader($identityRepository);

        $this->assertNull(
            $authMethod->authenticate(
                $this->createRequest(['X-Api-Key' => 'api-key'])
            )
        );
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response(400);
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpHeader($identityRepository);

        $this->assertEquals(400, $authMethod->challenge($response)->getStatusCode());
    }

    public function testEmptyTokenHeader(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpHeader($identityRepository);

        $this->assertNull(
            $authMethod->authenticate(
                $this->createRequest()
            )
        );
    }

    public function testCustomHeaderName(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpHeader($identityRepository);
        $authMethod->setHeaderName('Auth');
        $result = $authMethod->authenticate(
            $this->createRequest(['Auth' => 'api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
    }

    public function testCustomPattern(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpHeader($identityRepository);
        $authMethod->setPattern('/^CustomTokenPrefix\s+(.*?)$/');
        $result = $authMethod->authenticate(
            $this->createRequest(['X-Api-Key' => 'CustomTokenPrefix api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals(
            [
                'findIdentityByToken' =>
                    [
                        'token' => 'api-key',
                        'type' => HttpHeader::class
                    ]
            ],
            $identityRepository->getCallParams()
        );
    }

    public function testCustomPatternThatDoesNotMatch(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpHeader($identityRepository);
        $authMethod->setPattern('/^not-match-regexp/');
        $result = $authMethod->authenticate(
            $this->createRequest(['X-Api-Key' => 'api-key'])
        );

        $this->assertNull($result);
    }

    private function createIdentity(): IdentityInterface
    {
        return new FakeIdentity('test-id');
    }

    private function createRequest(array $headers = []): ServerRequestInterface
    {
        return new ServerRequest(Method::GET, '/', $headers);
    }
}
