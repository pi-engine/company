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
        StreamFactoryInterface   $streamFactory,
        CompanyService           $companyService
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
                    'value' => 'Manager',
                ],
                [
                    'key'   => $this->companyService->companyGovernanceManager,
                    'value' => 'Governance Manager',
                ],
                [
                    'key'   => $this->companyService->companyAssessmentManager,
                    'value' => 'Assessment Manager',
                ],
                [
                    'key'   => $this->companyService->companyAssessmentOfficer,
                    'value' => 'Assessment Officer',
                ],
                [
                    'key'   => $this->companyService->companyComplianceManager,
                    'value' => 'Compliance Manager',
                ],
                [
                    'key'   => $this->companyService->companyComplianceOfficer,
                    'value' => 'Compliance Officer',
                ],
                [
                    'key'   => $this->companyService->companyRiskManager,
                    'value' => 'Risk Manager',
                ],
                [
                    'key'   => $this->companyService->companyRiskOfficer,
                    'value' => 'Risk Officer',
                ],
                [
                    'key'   => $this->companyService->companyAuditManager,
                    'value' => 'Audit Manager',
                ],
                [
                    'key'   => $this->companyService->companyAuditor,
                    'value' => 'Auditor',
                ],
            ],
            'error'  => [],
        ];

        return new EscapingJsonResponse($result, $result['status'] ?? StatusCodeInterface::STATUS_OK);
    }
}