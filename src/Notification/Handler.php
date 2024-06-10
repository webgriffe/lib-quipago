<?php

namespace Webgriffe\LibQuiPago\Notification;

use Psr\Http\Message\ServerRequestInterface;

interface Handler
{
    public function handle(ServerRequestInterface $httpRequest, string $secretKey, string $macMethod): Result;
}
