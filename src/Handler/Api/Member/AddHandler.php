<?php

namespace Pi\Company\Handler\Api\Member;

use Fig\Http\Message\StatusCodeInterface;
use Pi\Company\Service\CompanyService;
use Pi\Core\Response\EscapingJsonResponse;
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
        StreamFactoryInterface   $streamFactory,
        CompanyService           $companyService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->companyService  = $companyService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $account       = $request->getAttribute('account');
        $authorization = $request->getAttribute('company_authorization');
        $requestBody   = $request->getParsedBody();

        // Add member
        $member = $this->companyService->addMemberByCompany($authorization['company'], $requestBody, $account);

        // Set result
        $result = [
            'result' => true,
            'data'   => $member,
            'error'  => [],
        ];

        return new EscapingJsonResponse($result, $result['status'] ?? StatusCodeInterface::STATUS_OK);
    }
}