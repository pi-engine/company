<?php

namespace Company\Handler\Api\Member;

use Company\Service\CompanyService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddHandler implements RequestHandlerInterface
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
        $account      = $request->getAttribute('account');
        $companyCheck = $request->getAttribute('company_check');
        $requestBody  = $request->getParsedBody();

        $params = [
            'company_id' => $companyCheck['data']['company_id'],
            'first_name' => $requestBody['first_name'] ?? '',
            'last_name'  => $requestBody['last_name'] ?? '',
            //'mobile'     => $requestBody['mobile'] ?? '',
            'email'      => $requestBody['email'] ?? '',
            'role'       => $requestBody['role'] ?? '',
            'added_by'   => $account
        ];

        // Set result
        $result = $this->companyService->addMember($params);

        return new JsonResponse($result);
    }
}