<?php
declare(strict_types=1);

namespace Szemul\Framework\Bootstrap;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Szemul\Config\ConfigInterface;
use Szemul\LoggingErrorHandling\Context\ContextInterface;
use Szemul\LoggingErrorHandling\Log\Monolog\Formatter\ContextAwareJsonFormatter;

class LoggingBootstrap implements BootstrapInterface
{
    public function __invoke(ContainerInterface $container): void
    {
        /** @var Logger $monolog */
        $monolog = $container->get(LoggerInterface::class);
        /** @var ContextInterface $context */
        $context = $container->get(ContextInterface::class);
        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);

        $logDir = $config->get('application.logDir');

        $fileHandler = new RotatingFileHandler($logDir . '/default', 3);
        $fileHandler->setFilenameFormat('{filename}-{date}.log', RotatingFileHandler::FILE_PER_DAY);
        $fileHandler->setFormatter(
            new ContextAwareJsonFormatter($context, ContextAwareJsonFormatter::BATCH_MODE_JSON, true),
        );

        $monolog->pushHandler($fileHandler);
        $monolog->pushHandler(new StreamHandler('php://stderr'));
    }
}
