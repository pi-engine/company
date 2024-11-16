<?php

namespace Pi\Company\Handler\Api\Member;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Pi\Company\Service\CompanyService;
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
                    'key'   => $this->companyService->companyAdminRole,
                    'value' => 'Full Admin',
                ],
                [
                    'key'   => $this->companyService->companySuperUserRole,
                    'value' => 'Super User',
                ],
                [
                    'key'   => $this->companyService->companyAssessmentManagerRole,
                    'value' => 'Assessment Manager',
                ],
                [
                    'key'   => $this->companyService->companyComplianceManagerRole,
                    'value' => 'Compliance Manager',
                ],
                [
                    'key'   => $this->companyService->companyRiskManagerRole,
                    'value' => 'Risk Manager',
                ],
                [
                    'key'   => $this->companyService->companyAuditManagerRole,
                    'value' => 'Audit Manager',
                ],
                [
                    'key'   => $this->companyService->companyComplianceOfficerRole,
                    'value' => 'Compliance Officer',
                ],
                [
                    'key'   => $this->companyService->companyAuditorRole,
                    'value' => 'Auditor',
                ],
                [
                    'key'   => $this->companyService->companyMemberRole,
                    'value' => 'Internal Member',
                ],
                [
                    'key'   => $this->companyService->companyExternalRole,
                    'value' => 'External Member',
                ],
                [
                    'key'   => $this->companyService->companyViewerRole,
                    'value' => 'Viewer',
                ],
            ],
            'error'  => [],
        ];

        return new JsonResponse($result, $result['status'] ?? StatusCodeInterface::STATUS_OK);
    }
}