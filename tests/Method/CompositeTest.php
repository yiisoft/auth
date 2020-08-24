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
use Yiisoft\Auth\Tests\Stub\FakeContainer;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Auth\Tests\Stub\FakeIdentityRepository;
use Yiisoft\Http\Method;

final class CompositeTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());

        $container = new FakeContainer();
        $authMethod = (new Composite($container));
        $authMethod->setAuthMethods(
            [
                new QueryParam($identityRepository),
                new HttpBearer($identityRepository)
            ]
        );

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

        $container = new FakeContainer();
        $authMethod = (new Composite($container));
        $authMethod->setAuthMethods(
            [
                new QueryParam($nullIdentityRepository),
                new HttpBearer($identityRepository)
            ]
        );

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
        $container = new FakeContainer();
        $result = (new Composite($container))->authenticate(
            $this->createRequest()
        );

        $this->assertNull($result);
    }

    public function testChallengeIsCorrect(): void
    {
        $response = new Response(400);
        $identityRepository = new FakeIdentityRepository($this->createIdentity());

        $container = new FakeContainer();
        $authMethod = (new Composite($container));
        $authMethod->setAuthMethods(
            [
                new QueryParam($identityRepository),
                new HttpBearer($identityRepository)
            ]
        );

        $this->assertEquals(
            'Authorization realm="api"',
            $authMethod->challenge($response)->getHeaderLine('WWW-Authenticate')
        );
    }

    public function testSuccessfulAuthenticationWithMethodsFromContainer(): void
    {
        $identityRepository = new FakeIdentityRepository($this->createIdentity());
        $container = new FakeContainer(
            [
                QueryParam::class => new QueryParam($identityRepository),
                HttpBearer::class => new HttpBearer($identityRepository)
            ]
        );

        $authMethod = (new Composite($container));
        $authMethod->setAuthMethods(
            [
                QueryParam::class,
                HttpBearer::class
            ]
        );

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

    public function testExpectInvalidDependencyFromContainer(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('stdClass must implement Yiisoft\Auth\AuthInterface');
        $container = new FakeContainer(
            [
                QueryParam::class => new \stdClass(),
            ]
        );
        $authMethod = (new Composite($container));
        $authMethod->setAuthMethods([QueryParam::class]);
        $authMethod->authenticate($this->createRequest(['Authorization' => 'Bearer api-key']));
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
