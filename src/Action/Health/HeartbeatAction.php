<?php
declare(strict_types=1);

namespace Szemul\Framework\Action\Health;

use Psr\Http\Message\ResponseInterface as Response;
use Szemul\Framework\Action\ActionAbstract;

class HeartbeatAction extends ActionAbstract
{
    protected function action(): Response
    {
        return $this->respondWithData(['heartbeat' => 'OK']);
    }
}
