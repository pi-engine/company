<?php

namespace Company\Handler\Api\Member;

use Company\Service\CompanyService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListHandler implements RequestHandlerInterface
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
        $authentication = $request->getAttribute('company_authentication');
        $requestBody  = $request->getParsedBody();

        $params = [
            'company_id' => $authentication['company_id'],
            'limit'      => $requestBody['limit'] ?? 25,
            'page'       => $requestBody['page'] ?? 1,
            'mobile'     => $requestBody['mobile'] ?? '',
            'email'      => $requestBody['email'] ?? '',
            'name'       => $requestBody['name'] ?? '',
        ];

        // Set result
        $result = $this->companyService->getMemberList($params);

        return new JsonResponse($result);
    }
}