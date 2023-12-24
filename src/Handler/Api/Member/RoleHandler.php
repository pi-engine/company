<?php

namespace Company\Handler\Api\Member;

use Company\Service\CompanyService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoleHandler implements RequestHandlerInterface
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
        // Set result
        $result = [
            'result' => true,
            'data'   => [
                [
                    'key' => 'companymember',
                    'value' => 'User (Internal)',
                ],
                [
                    'key' => 'companyexternal',
                    'value' => 'User (External)',
                ],
                [
                    'key' => 'companymanager',
                    'value' => 'Manager',
                ],
                [
                    'key' => 'companyaudit',
                    'value' => 'Auditor',
                ],
                [
                    'key' => 'companyadmin',
                    'value' => 'Full Admin',
                ]
            ],
            'error'  => [],
        ];

        return new JsonResponse($result, $result['status'] ?? StatusCodeInterface::STATUS_OK);
    }
}