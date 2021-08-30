<?php
declare(strict_types=1);

namespace Szemul\Framework\Bootstrap;

use Psr\Container\ContainerInterface;
use Szemul\Debugger\Registry\DebuggerRegistry;

class DebuggerBootstrap implements BootstrapInterface
{
    /** @var string[] */
    protected array $debuggerClasses;

    public function __construct(string ...$debuggerClasses)
    {
        $this->debuggerClasses = $debuggerClasses;
    }

    public function __invoke(ContainerInterface $container): void
    {
        /** @var DebuggerRegistry $debugger */
        $debugger = $container->get(DebuggerRegistry::class);

        foreach ($this->debuggerClasses as $debuggerClass) {
            $debugger->addDebugger($container->get($debuggerClass));
        }
    }
}
