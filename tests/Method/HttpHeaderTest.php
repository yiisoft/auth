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
        $authenticationMethod = new HttpHeader($identityRepository);

        $this->assertNull(
            $authenticationMethod->authenticate(
                $this->createRequest(['X-Api-Key' => 'api-key'])
            )
        );
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response(400);
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authenticationMethod = new HttpHeader($identityRepository);

        $this->assertEquals(400, $authenticationMethod->challenge($response)->getStatusCode());
    }

    public function testEmptyTokenHeader(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authenticationMethod = new HttpHeader($identityRepository);

        $this->assertNull(
            $authenticationMethod->authenticate(
                $this->createRequest()
            )
        );
    }

    public function testCustomHeaderName(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authenticationMethod = (new HttpHeader($identityRepository))
            ->withHeaderName('Auth');
        $result = $authenticationMethod->authenticate(
            $this->createRequest(['Auth' => 'api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
    }

    public function testCustomPattern(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authenticationMethod = (new HttpHeader($identityRepository))
            ->withPattern('/^CustomTokenPrefix\s+(.*?)$/');
        $result = $authenticationMethod->authenticate(
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
        $authenticationMethod = (new HttpHeader($identityRepository))
            ->withPattern('/^not-match-regexp/');
        $result = $authenticationMethod->authenticate(
            $this->createRequest(['X-Api-Key' => 'api-key'])
        );

        $this->assertNull($result);
    }

    public function testImmutability(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $original = (new HttpHeader($identityRepository));
        $this->assertNotSame($original, $original->withHeaderName('headerName'));
        $this->assertNotSame($original, $original->withPattern('pattern'));
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
