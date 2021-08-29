<?php
declare(strict_types=1);

namespace Szemul\Framework\Bootstrap;

use Psr\Container\ContainerInterface;

interface BootstrapInterface
{
    public function __invoke(ContainerInterface $container): void;
}
