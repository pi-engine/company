<?php

namespace Company\Middleware;

use Company\Service\CompanyService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Handler\ErrorHandler;

class CheckMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var ErrorHandler */
    protected ErrorHandler $errorHandler;

    /** @var CompanyService */
    protected CompanyService $companyService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ErrorHandler $errorHandler,
        CompanyService $companyService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->errorHandler    = $errorHandler;
        $this->companyService  = $companyService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $account     = $request->getAttribute('account');
        $requestBody = $request->getParsedBody();
        $checkResult = $this->companyService->check($account, $requestBody);

        if (!$checkResult['result']) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_UNAUTHORIZED);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => $checkResult['error']['message'],
                    'code'    => StatusCodeInterface::STATUS_UNAUTHORIZED,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        $request = $request->withAttribute('company_check', $checkResult);

        return $handler->handle($request);
    }
}