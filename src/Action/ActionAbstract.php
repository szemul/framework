<?php
declare(strict_types=1);

namespace Szemul\Framework\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpSpecializedException;

abstract class ActionAbstract
{
    /** @var array<string,string> */
    protected array    $args;
    protected Request  $request;
    protected Response $response;

    /**
     * @param array<string,string> $args
     *
     * @throws HttpSpecializedException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request  = $request;
        $this->response = $response;
        $this->args     = $args;

        return $this->action();
    }

    /**
     * @throws HttpSpecializedException
     */
    abstract protected function action(): Response;

    /**
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name): mixed
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /** @param mixed[]|object|null $data */
    protected function respondWithData(array|object|null $data = null, int $statusCode = 200): Response
    {
        if (null !== $data) {
            $json = json_encode($data, JSON_PRETTY_PRINT);
            $this->response->getBody()
                ->write($json);
        }

        return $this->response->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
