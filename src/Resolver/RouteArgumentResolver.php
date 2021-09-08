<?php
declare(strict_types=1);

namespace Szemul\Framework\Resolver;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\Route;

class RouteArgumentResolver
{
    /**
     * @throws HttpBadRequestException
     */
    public function resolveArgument(ServerRequestInterface $request, string $argument): string
    {
        /** @var Route|null $route */
        $route = $request->getAttribute('__route__');

        if (null === $route) {
            throw new HttpBadRequestException($request);
        }

        $value = $route->getArgument($argument);

        if (null === $value) {
            throw new HttpBadRequestException($request);
        }

        return $value;
    }
}
