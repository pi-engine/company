<?php

namespace Pi\Company\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Pi\Company\Service\CompanyService;
use Pi\Core\Handler\ErrorHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CompanyMiddleware implements MiddlewareInterface
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
        StreamFactoryInterface   $streamFactory,
        ErrorHandler             $errorHandler,
        CompanyService           $companyService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->errorHandler    = $errorHandler;
        $this->companyService  = $companyService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $account       = $request->getAttribute('account');
        $tokenData     = $request->getAttribute('token_data');
        $requestBody   = $request->getParsedBody();
        $authorization = $this->companyService->authorization($account, $requestBody);

        // Check authorization
        if (!$authorization['result']) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_UNAUTHORIZED);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => $authorization['error']['message'],
                    'code'    => StatusCodeInterface::STATUS_UNAUTHORIZED,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        // Check authorization and token company_id is same
        if (
            (
                !isset($tokenData['company_id'])
                || empty($tokenData['company_id'])
                || (int)$tokenData['company_id'] === 0
                || (int)$tokenData['company_id'] !== (int)$authorization['data']['company_id']
            )
            && (
                $tokenData['type'] != 'refresh'
            )
        ) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_UPGRADE_REQUIRED);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'Company ID not found, please refresh your token !',
                    'code'    => StatusCodeInterface::STATUS_UPGRADE_REQUIRED,
                ]
            );
            $request = $request->withAttribute(
                'header',
                [
                    'Upgrade'         => 'refresh-token',
                    'X-Upgrade-Token' => 'Please refresh your token by calling /company/authentication/refresh-token, by refresh_token',
                ]
            );
            return $this->errorHandler->handle($request);
        }

        $request = $request->withAttribute('company_authorization', $authorization['data']);
        return $handler->handle($request);
    }
}