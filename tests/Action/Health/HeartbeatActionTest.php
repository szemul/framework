<?php
declare(strict_types=1);

namespace Szemul\Framework\Test\Action\Health;

use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use Szemul\Framework\Action\Health\HeartbeatAction;
use PHPUnit\Framework\TestCase;

class HeartbeatActionTest extends TestCase
{
    public function testAction(): void
    {
        $sut = new HeartbeatAction();

        /** @var ServerRequestInterface $request */
        $request  = Mockery::mock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = new Response();

        $result = $sut($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals(['heartbeat' => 'OK'], json_decode((string)$result->getBody(), true));
    }

    public function testDebugInfo(): void
    {
        $sut = new HeartbeatAction();

        $this->assertSame([], $sut->__debugInfo());
    }
}
