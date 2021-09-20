<?php
declare(strict_types=1);

namespace Szemul\Framework\Bootstrap;

use DI\ContainerBuilder;
use Exception;
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
    /** @var ConfigBuilderInterface[] */
    protected array $configBuilders =[];
    /** @var DefinitionProviderInterface[] */
    protected array              $definitionProviders = [];
    protected bool               $isStarted           = false;
    protected ContainerInterface $container;

    public function __construct(
        protected string $rootDirPath,
        protected ConfigInterface $config,
        string ...$commonBoostrappers,
    ) {
        $this->commonBootstrappers = $commonBoostrappers;
    }

    /** @return array<string,mixed> */
    public function __debugInfo(): ?array
    {
        return [
            'commonBootstrappers' => $this->commonBootstrappers,
            'configBuilders'      => $this->configBuilders,
            'definitionProviders' => $this->definitionProviders,
            'isStarted'           => $this->isStarted,
            'container'           => '*** Instance of ' . get_class($this->container),
            'rootDirPath'         => $this->rootDirPath,
            'configInterface'     => '*** Instance of ' . get_class($this->config),
        ];
    }

    public function start(string $appName): ContainerInterface
    {
        if ($this->isStarted) {
            throw new \RuntimeException('The bootstrap process has already been started');
        }

        $environmentHandler = $this->loadEnvironmentHandler(realpath($this->rootDirPath . '/.env'));

        $this->buildConfig($appName, $environmentHandler);

        $this->container = $this->buildContainer($appName, $environmentHandler);

        $this->runBootstrappers(
            ...array_map(fn (string $className) => $this->container->get($className), $this->commonBootstrappers),
        );

        $this->isStarted = true;

        return $this->container;
    }

    public function addConfigBuilders(ConfigBuilderInterface ...$configBuilders): static
    {
        if ($this->isStarted) {
            throw new \RuntimeException(
                'The bootstrap process has already been started. Adding configBuilders after start is not allowed',
            );
        }

        $this->configBuilders = array_merge($this->configBuilders, $configBuilders);

        return $this;
    }

    public function addDefinitionProviders(DefinitionProviderInterface ...$definitionProviders): static
    {
        if ($this->isStarted) {
            throw new \RuntimeException(
                'The bootstrap process has already been started. Adding definitionProviders after start is not allowed',
            );
        }

        $this->definitionProviders = array_merge($this->definitionProviders, $definitionProviders);

        return $this;
    }

    /** @return ConfigBuilderInterface[] */
    public function getConfigBuilders(): array
    {
        return $this->configBuilders;
    }

    /** @return DefinitionProviderInterface[] */
    public function getDefinitionProviders(): array
    {
        return $this->definitionProviders;
    }

    public function runBootstrappers(BootstrapInterface ...$bootstrappers): void
    {
        foreach ($bootstrappers as $bootstrapper) {
            $bootstrapper($this->container);
        }
    }

    protected function loadEnvironmentHandler(string ...$dotEnvs): EnvironmentHandlerInterface
    {
        return new EnvironmentHandler(...$dotEnvs);
    }

    protected function buildConfig(
        string $appName,
        EnvironmentHandlerInterface $environmentHandler,
    ): void {
        $compileConfig = $environmentHandler->getValue('APP_COMPILE_CONFIG', false);

        if ($compileConfig) {
            $cacheFile = $this->getCacheDir($environmentHandler, 'config') . $appName . '.php';

            if (file_exists($cacheFile) && is_readable($cacheFile)) {
                $this->config->setArray(include $cacheFile);
            }
        }

        foreach ($this->configBuilders as $configBuilder) {
            $configBuilder->build($environmentHandler, $this->config);
        }

        if ($compileConfig) {
            file_put_contents($cacheFile, "<?php\nreturn " . var_export($this->config->toArray(), true) . ";\n");
        }
    }

    /**
     * @throws Exception
     */
    protected function buildContainer(
        string $appName,
        EnvironmentHandlerInterface $environmentHandler,
    ): ContainerInterface {
        $containerBuilder = new ContainerBuilder();
        $compileContainer = $environmentHandler->getValue('APP_COMPILE_CONTAINER', false);

        if ($compileContainer) {
            $containerBuilder->enableCompilation(
                $this->getCacheDir($environmentHandler, 'container'),
                'Compiled' . $appName . 'Container',
            );
        }

        foreach ($this->definitionProviders as $definitionProvider) {
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
