<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Method;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\HttpBearer;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Method;

final class HttpBearerTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $result = (new HttpBearer($identityRepository))->authenticate(
            $this->createRequest(['Authorization' => 'Bearer api-key'])
        );

        $this->assertNotNull($result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals(
            [
                'findIdentityByToken' => [
                    'token' => 'api-key',
                    'type' => HttpBearer::class
                ]
            ],
            $identityRepository->getCallParams()
        );
    }

    public function testIdentityNotFoundByToken(): void
    {
        $identityRepository = new FakeIdentityRepository(null);
        $authMethod = new HttpBearer($identityRepository);

        $this->assertNull(
            $authMethod->authenticate(
                $this->createRequest(['Authorization' => 'Bearer api-key'])
            )
        );
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response();
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpBearer($identityRepository);

        $this->assertEquals(
            'Authorization realm="api"',
            $authMethod->challenge($response)->getHeaderLine('WWW-Authenticate')
        );
    }

    public function testCustomRealm(): void
    {
        $response = new Response();
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $authMethod = new HttpBearer($identityRepository);
        $authMethod->setRealm('gateway');

        $this->assertEquals(
            'Authorization realm="gateway"',
            $authMethod->challenge($response)->getHeaderLine('WWW-Authenticate')
        );
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
