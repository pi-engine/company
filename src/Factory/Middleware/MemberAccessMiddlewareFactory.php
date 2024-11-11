<?php

namespace Company\Factory\Middleware;

use Company\Middleware\MemberAccessMiddleware;
use Company\Service\CompanyService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Core\Handler\ErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MemberAccessMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MemberAccessMiddleware
    {
        return new MemberAccessMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(ErrorHandler::class),
            $container->get(CompanyService::class)
        );
    }
}