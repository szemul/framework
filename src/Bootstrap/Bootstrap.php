<?php
declare(strict_types=1);

namespace Szemul\Framework\Bootstrap;

use DI\Container;
use DI\ContainerBuilder;
use JetBrains\PhpStorm\Pure;
use Psr\Container\ContainerInterface;
use Szemul\Config\Builder\ConfigBuilderInterface;
use Szemul\Config\ConfigInterface;
use Szemul\Config\Environment\EnvironmentHandler;
use Szemul\Config\Environment\EnvironmentHandlerInterface;
use Szemul\DependencyInjection\Provider\DefinitionProviderInterface;

class Bootstrap
{
    /** @var string[] */
    protected array $commonBootstrappers;
    protected ContainerInterface $container;

    public function __construct(
        protected string $rootDirPath,
        protected ConfigInterface $config,
        protected ConfigBuilderInterface $commonConfigBuilder,
        protected DefinitionProviderInterface $commonDefinitionProvider,
        string ...$commonBoostrappers,
    ) {
        $this->commonBootstrappers = $commonBoostrappers;
    }

    public function start(
        string $appName,
        ?DefinitionProviderInterface $definitionProvider = null,
        ?ConfigBuilderInterface $configBuilder = null,
    ): Container {
        $configBuilders = [$this->getCommonConfigBuilder()];

        if (null !== $configBuilder) {
            $configBuilders[] = $configBuilder;
        }

        $environmentHandler = $this->loadEnvironmentHandler(realpath($this->rootDirPath . '/.env'));
        $this->buildConfig($appName, $environmentHandler, ...$configBuilders);

        $definitionProviders = [$this->getCommonDefinitionProvider()];

        if (null !== $definitionProvider) {
            $definitionProviders[] = $definitionProvider;
        }

        $this->container = $this->buildContainer($appName, $environmentHandler, ...$definitionProviders);

        return $this->container;
    }

    public function runBootstrappers(BootstrapInterface ...$additionalBootstrappers): void
    {
        $bootstrappers = array_merge(
            array_map(fn (string $className) => $this->container->get($className), $this->commonBootstrappers),
            $additionalBootstrappers,
        );

        foreach ($bootstrappers as $bootstrapper) {
            $bootstrapper($this->container);
        }
    }

    #[Pure]
    protected function getCommonConfigBuilder(): ConfigBuilderInterface
    {
        return $this->commonConfigBuilder;
    }

    #[Pure]
    protected function getCommonDefinitionProvider(): DefinitionProviderInterface
    {
        return $this->commonDefinitionProvider;
    }

    protected function loadEnvironmentHandler(string ...$dotEnvs): EnvironmentHandlerInterface
    {
        return new EnvironmentHandler(...$dotEnvs);
    }

    protected function buildConfig(
        string $appName,
        EnvironmentHandlerInterface $environmentHandler,
        ConfigBuilderInterface ...$configBuilders,
    ): void {
        $compileConfig = $environmentHandler->getValue('APP_COMPILE_CONFIG', false);

        if ($compileConfig) {
            $cacheFile = $this->getCacheDir($environmentHandler, 'config') . $appName . '.php';

            if (file_exists($cacheFile) && is_readable($cacheFile)) {
                $this->config->setArray(include $cacheFile);
            }
        }

        foreach ($configBuilders as $configBuilder) {
            $configBuilder->build($environmentHandler, $this->config);
        }

        if ($compileConfig) {
            file_put_contents($cacheFile, "<?php\nreturn " . var_export($this->config->toArray(), true) . ";\n");
        }
    }

    protected function buildContainer(
        string $appName,
        EnvironmentHandlerInterface $environmentHandler,
        DefinitionProviderInterface ...$definitionProviders,
    ): Container {
        $containerBuilder = new ContainerBuilder();
        $compileContainer = $environmentHandler->getValue('APP_COMPILE_CONTAINER', false);

        if ($compileContainer) {
            $containerBuilder->enableCompilation(
                $this->getCacheDir($environmentHandler, 'container'),
                'Compiled' . $appName . 'Container',
            );
        }

        foreach ($definitionProviders as $definitionProvider) {
            $containerBuilder->addDefinitions($definitionProvider->getDefinitions());
        }

        return $containerBuilder->build();
    }

    protected function getCacheDir(EnvironmentHandlerInterface $environmentHandler, string $cacheType): string
    {
        $cacheDir = rtrim($environmentHandler->getValue('APP_CACHE_DIR_PATH', $this->rootDirPath . '/var/cache'), '/');

        return $cacheDir . '/' . $cacheType . '/';
    }
}
