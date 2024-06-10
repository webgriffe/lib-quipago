<?php

namespace Webgriffe\LibQuiPago\Notification;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\Checker;
use Webgriffe\LibQuiPago\Signature\InvalidMacException;
use Webgriffe\LibQuiPago\Signature\DefaultChecker;

class DefaultHandler implements Handler
{
    private Checker $checker;

    private ?LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null, Checker $checker = null)
    {
        if (!$checker) {
            $checker = new DefaultChecker($logger);
        }
        $this->checker = $checker;
        $this->logger = $logger;
    }

    /**
     * Handle notification request
     *
     * @throws InvalidMacException
     */
    public function handle(ServerRequestInterface $httpRequest, string $secretKey, string $macMethod): Result
    {
        if ($this->logger) {
            $this->logger->debug(sprintf('%s method called', __METHOD__));
            $this->logger->debug(sprintf('Secret key: "%s"', $secretKey));
            $this->logger->debug(sprintf('Request body: %s', json_encode($httpRequest->getParsedBody())));
            $this->logger->debug(sprintf('Request query: %s', json_encode($httpRequest->getQueryParams())));
        }
        $request = Request::buildFromHttpRequest($httpRequest);
        $this->checker->checkSignature($request, $secretKey, $macMethod);

        return new Result($request);
    }
}
