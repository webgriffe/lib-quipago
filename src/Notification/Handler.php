<?php

namespace Webgriffe\LibQuiPago\Notification;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\SignatureChecker;
use Webgriffe\LibQuiPago\Signature\SignatureHashingManager;

class Handler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Handler constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
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
            $this->logger->debug(sprintf('Request params: %s', json_encode($httpRequest->getParsedBody())));
        }

        $request = Request::buildFromHttpRequest($httpRequest);
        $signatureChecker = new SignatureChecker($this->logger, new SignatureHashingManager());
        $signatureChecker->checkSignature($request, $secretKey, $macMethod);

        return new Result($request);
    }
}
