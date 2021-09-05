<?php
declare(strict_types=1);

namespace Szemul\Framework\Robo\OpenApi;

use Szemul\Framework\Robo\ApplicationConfig;

interface GeneratorInterface
{
    /** @param array<string,array> $jsonContent */
    public function addErrorsToOpenApiJsonContent(array &$jsonContent, ApplicationConfig $config): void;
}
