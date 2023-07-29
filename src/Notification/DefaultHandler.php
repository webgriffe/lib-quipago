<?php

namespace Webgriffe\LibQuiPago\Notification;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\Checker;
use Webgriffe\LibQuiPago\Signature\InvalidMacException;
use Webgriffe\LibQuiPago\Signature\DefaultChecker;

class DefaultHandler implements Handler
{
    private \Webgriffe\LibQuiPago\Signature\Checker $checker;

    private ?\Psr\Log\LoggerInterface $logger = null;

    /**
     * Handler constructor.
     */
    public function __construct(LoggerInterface $logger = null, Checker $checker = null)
    {
        if (!$checker instanceof \Webgriffe\LibQuiPago\Signature\Checker) {
            $checker = new DefaultChecker($logger);
        }

        $this->checker = $checker;
        $this->logger = $logger;
    }

    /**
     * Handle notification request
     * @param ServerRequestInterface $serverRequest Notify request coming from Quipago
     * @param string $secretKey Secret key for MAC calculation
     * @param string $macMethod MAC calculation method. It should be 'sha1' or 'md5'
     *
     * @throws InvalidMacException
     */
    public function handle(ServerRequestInterface $serverRequest, $secretKey, $macMethod): \Webgriffe\LibQuiPago\Notification\Result
    {
        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug(sprintf('%s method called', __METHOD__));
            $this->logger->debug(sprintf('Secret key: "%s"', $secretKey));
            $this->logger->debug(sprintf('Request body: %s', json_encode($serverRequest->getParsedBody(), JSON_THROW_ON_ERROR)));
            $this->logger->debug(sprintf('Request query: %s', json_encode($serverRequest->getQueryParams(), JSON_THROW_ON_ERROR)));
        }

        $request = Request::buildFromHttpRequest($serverRequest);
        $this->checker->checkSignature($request, $secretKey, $macMethod);

        return new Result($request);
    }
}
