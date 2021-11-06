<?php
declare(strict_types=1);

namespace Szemul\Framework\Test\Resolver;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\Route;
use Szemul\Framework\Resolver\RouteArgumentResolver;
use PHPUnit\Framework\TestCase;

class RouteArgumentResolverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ServerRequestInterface|MockInterface|LegacyMockInterface $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Mockery::mock(ServerRequestInterface::class);
    }

    public function testResolveArgumentWithSetArgument_shouldReturnTheArgumentValue(): void
    {
        $key   = 'arg';
        $value = 'argValue';
        $sut   = new RouteArgumentResolver();
        $route = $this->expectRouteSet();
        $this->expectAttributeRetrievedFromRoute($route, $key, $value);

        $this->assertSame($value, $sut->resolveArgument($this->request, $key));
    }

    public function testResolveArgumentWithNotSentArgument_shouldThrowException(): void
    {
        $key   = 'arg';
        $sut   = new RouteArgumentResolver();
        $route = $this->expectRouteSet();
        $this->expectAttributeRetrievedFromRoute($route, $key);
        $this->expectException(HttpBadRequestException::class);

        $this->assertNull($sut->resolveArgument($this->request, $key));
    }

    public function testResolveArgumentWithNoRouteSet_shouldThrowException(): void
    {
        $key   = 'arg';
        $sut   = new RouteArgumentResolver();
        $this->expectNoRouteSet();
        $this->expectException(HttpBadRequestException::class);

        $this->assertNull($sut->resolveArgument($this->request, $key));
    }

    private function expectNoRouteSet(): void
    {
        //@phpstan-ignore-next-line
        $this->request->shouldReceive('getAttribute')
            ->once()
            ->with('__route__')
            ->andReturnNull();
    }

    private function expectRouteSet(): Route|MockInterface|LegacyMockInterface
    {
        $route = Mockery::mock(Route::class);

        //@phpstan-ignore-next-line
        $this->request->shouldReceive('getAttribute')
            ->once()
            ->with('__route__')
            ->andReturn($route);

        return $route;
    }

    private function expectAttributeRetrievedFromRoute(
        Route|MockInterface|LegacyMockInterface $route,
        string $key,
        ?string $value = null,
    ): void {
        //@phpstan-ignore-next-line
        $route->shouldReceive('getArgument')
            ->once()
            ->with($key)
            ->andReturn($value);
    }
}
