<?php
declare(strict_types=1);

namespace Szemul\Framework\Bootstrap;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Szemul\Framework\Router\RouterInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\ResponseEmitter;
use Szemul\Config\ConfigInterface;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Szemul\SlimErrorHandlerBridge\Handler\ErrorHandler;
use Szemul\SlimErrorHandlerBridge\Renderer\JsonErrorRenderer;
use Szemul\SlimSentryBridge\Middleware\SentryMiddleware;

class AppBootstrap implements BootstrapInterface
{
    /** @var MiddlewareInterface[] */
    protected array $middlewares;

    public function __construct(protected ?RouterInterface $router = null, MiddlewareInterface ...$middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /** @return array<string,mixed>|null */
    public function __debugInfo(): ?array
    {
        return [
            'middlewares' => array_map(fn ($value) => '*** Instance of ' . get_class($value), $this->middlewares),
            'router'      => '*** Instance of ' . get_class($this->router),
        ];
    }

    public function __invoke(ContainerInterface $container): void
    {
        $app = $this->setupApp($container);

        $this->addMiddlewares($app);

        $this->addSystemMiddlewares($app, $container);

        $this->setRoutes($app);

        $response = $app->handle(ServerRequestCreatorFactory::create()->createServerRequestFromGlobals());

        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }

    protected function setupApp(ContainerInterface $container): App
    {
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        return $app;
    }

    protected function addMiddlewares(App $app): void
    {
        foreach ($this->middlewares as $middleware) {
            $app->add($middleware);
        }
    }

    protected function addSystemMiddlewares(App $app, ContainerInterface $container): void
    {
        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);

        $app->add(SentryMiddleware::class);
        $app->addRoutingMiddleware();
        $errorMiddleware = $app->addErrorMiddleware(
            $config->get('system.displayErrorDetails', false),
            false,
            false,
        );

        $errorHandler = new ErrorHandler(
            $container->get(ErrorHandlerRegistry::class),
            $app->getCallableResolver(),
            $app->getResponseFactory(),
        );

        $jsonRenderer = $container->get(JsonErrorRenderer::class);
        $errorHandler->registerErrorRenderer('application/json', $jsonRenderer);

        $errorMiddleware->setDefaultErrorHandler($errorHandler);
    }

    protected function setRoutes(App $app): void
    {
        if (null === $this->router) {
            return;
        }

        $router = $this->router;

        $router($app);
    }
}
