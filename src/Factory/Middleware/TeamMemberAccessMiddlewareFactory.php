<?php

namespace Pi\Company\Factory\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Middleware\TeamMemberAccessMiddleware;
use Pi\Company\Service\CompanyService;
use Pi\Core\Handler\ErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class TeamMemberAccessMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TeamMemberAccessMiddleware
    {
        return new TeamMemberAccessMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(ErrorHandler::class),
            $container->get(CompanyService::class)
        );
    }
}