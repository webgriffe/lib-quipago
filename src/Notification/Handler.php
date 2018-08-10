<?php
/**
 * Created by PhpStorm.
 * User: kraken
 * Date: 10/08/18
 * Time: 15.36
 */

namespace Webgriffe\LibQuiPago\Notification;

use Psr\Http\Message\ServerRequestInterface;

interface Handler
{
    public function handle(ServerRequestInterface $httpRequest, $secretKey, $macMethod);
}
