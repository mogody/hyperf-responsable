<?php

declare(strict_types=1);
/**
 * This file is part of mogody/hyperf-responsable.
 *
 * @link     https://github.com/mogody/hyperf-responsable
 * @document https://github.com/mogody/hyperf-responsable/blob/master/README.md
 * @contact  wenghang1228@gmail.com
 * @license  https://github.com/mogody/hyperf-responsable/blob/master/LICENSE
 */
namespace HyperfTest\Cases;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollector;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface as HyperfRequestInterface;
use Hyperf\HttpServer\Response;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Context;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use Mockery;
use Mogody\Responsable\Contract\Responsable;
use Mogody\Responsable\CoreMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
class ApiResourceTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testHelloWorldResponse()
    {
        $response = $this->getResponse(function () {
            return new HelloWorldResponse();
        });
        $this->assertSame('Hello World', $response->getBody()->getContents());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn(new DispatcherFactory());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)
            ->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);
        $container->shouldReceive('get')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(new ClosureDefinitionCollector());
        $container->shouldReceive('get')->with(NormalizerInterface::class)
            ->andReturn(new SimpleNormalizer());
        return $container;
    }

    protected function getResponse($except)
    {
        $middleware = new CoreMiddlewareStub($container = $this->getContainer(), 'http');
        $reflectionMethod = new ReflectionMethod(CoreMiddleware::class, 'transferToResponse');
        $reflectionMethod->setAccessible(true);
        $request = Mockery::mock(HyperfRequestInterface::class);
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());
        return $reflectionMethod->invoke($middleware, $except(), $request);
    }
}

class HelloWorldResponse implements Responsable
{
    public function toResponse(PsrRequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Context::get(ResponseInterface::class, $psr7Response = new Psr7Response());
        return $response->withBody(new SwooleStream('Hello World'));
    }
}

class CoreMiddlewareStub extends CoreMiddleware
{
    public function parseParameters(string $controller, string $action, array $arguments): array
    {
        return parent::parseParameters($controller, $action, $arguments);
    }

    protected function response(): ResponseInterface
    {
        return new Response();
    }
}
