<?php

namespace Webgriffe\LibQuiPago\Notification;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\Checker;
use Webgriffe\LibQuiPago\Signature\InvalidMacException;
use Webgriffe\LibQuiPago\Signature\DefaultChecker;

class DefaultHandler implements Handler
{
    /**
     * @var Checker
     */
    private $checker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Handler constructor.
     * @param LoggerInterface $logger
     */
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
     * @param ServerRequestInterface $httpRequest Notify request coming from Quipago
     * @param string $secretKey Secret key for MAC calculation
     * @param string $macMethod MAC calculation method. It should be 'sha1' or 'md5'
     *
     * @return Result
     * @throws InvalidMacException
     */
    public function handle(ServerRequestInterface $httpRequest, $secretKey, $macMethod)
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
