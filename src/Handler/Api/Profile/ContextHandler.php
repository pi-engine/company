<?php

namespace Company\Handler\Api\Profile;

use Company\Service\CompanyService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContextHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var CompanyService */
    protected CompanyService $companyService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        CompanyService $companyService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->companyService  = $companyService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $authorization = $request->getAttribute('company_authorization');
        $requestBody  = $request->getParsedBody();

        $result = $this->companyService->updateCompanyContext($authorization, $requestBody);

        return new JsonResponse($result);
    }
}