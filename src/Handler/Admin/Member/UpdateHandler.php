<?php

namespace Company\Handler\Admin\Member;

use Company\Service\CompanyService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
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
        $account     = $request->getAttribute('account');
        $requestBody   = $request->getParsedBody();

        $member = $this->companyService->updateMemberByAdmin($requestBody, $account);

        // Set result
        $result = [
            'result' => true,
            'data'   => $member,
            'error'  => [],
        ];

        return new JsonResponse($result, $result['status'] ?? StatusCodeInterface::STATUS_OK);
    }
}