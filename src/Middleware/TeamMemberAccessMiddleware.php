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

class TeamMemberAccessMiddleware implements MiddlewareInterface
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
        $roles         = $request->getAttribute('roles');
        $authorization = $request->getAttribute('company_authorization');
        $requestBody   = $request->getParsedBody();

        // Check ID is set
        if (empty($requestBody['id']) || !is_numeric($requestBody['id'])) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_BAD_REQUEST);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'You should set team member ID',
                    'code'    => StatusCodeInterface::STATUS_BAD_REQUEST,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        // Get team member
        $teamMember = $this->companyService->getTeamMember($requestBody['id']);

        // Check team member
        if (empty($teamMember) || (int)$teamMember['status'] !== 1) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'You should select team member !',
                    'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        // Check team member
        if ((int)$teamMember['company_id'] !== $authorization['company_id']) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'You dont have a access to this team member !',
                    'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        // Check access
        if (!in_array('companyadmin', $roles)) {
                    if (!in_array('companyassessmentmanager', $roles)) {
                    if (!in_array('companycompliancemanager', $roles)) {
                        if (!in_array('companyriskmanager', $roles)) {
                            if (!in_array('companyauditmanager', $roles)) {
                                $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
                                $request = $request->withAttribute(
                                    'error',
                                    [
                                        'message' => 'You are not authorized to update this item. Please contact admin for assistance.',
                                        'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                                    ]
                                );
                                return $this->errorHandler->handle($request);
                            }

                    }
                }
            }
        }

        $request = $request->withAttribute('team_member_item', $teamMember);

        return $handler->handle($request);
    }
}