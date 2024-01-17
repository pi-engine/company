<?php

namespace Company\Middleware;

use Company\Service\CompanyService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Handler\ErrorHandler;

class PackageMiddleware implements MiddlewareInterface
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
        StreamFactoryInterface $streamFactory,
        ErrorHandler $errorHandler,
        CompanyService $companyService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->errorHandler    = $errorHandler;
        $this->companyService  = $companyService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getAttribute('company_authorization');
        $routeMatch    = $request->getAttribute('Laminas\Router\RouteMatch');
        $routeParams   = $routeMatch->getParams();

        // Get and check package
        $package = $this->companyService->getPackage($authorization['package_id']);
        if (empty($package) || empty($package['information']) || (int)$package['status'] !== 1) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'Selected package not activate or exist !',
                    'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                    'type'    => 'package',
                ]
            );
            return $this->errorHandler->handle($request);
        }

        // Check service permission
        if (!isset($routeParams['permissions']) || empty($routeParams['permissions'])) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'Your selected service dose not have a true permissions !',
                    'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                    'type'    => 'package',
                ]
            );
            return $this->errorHandler->handle($request);
        }

        // Check package access
        if (!in_array($routeParams['permissions'], $package['information']['access'])) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'Your package dose not have a access to this area, for upgrade your package please contact to system admin !',
                    'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                    'type'    => 'package',
                ]
            );
            return $this->errorHandler->handle($request);
        }

        $request = $request->withAttribute('package', $package);
        return $handler->handle($request);
    }
}