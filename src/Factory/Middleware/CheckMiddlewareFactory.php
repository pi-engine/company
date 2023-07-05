<?php

namespace Company\Factory\Middleware;

use Company\Middleware\CheckMiddleware;
use Company\Service\CompanyService;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use User\Handler\ErrorHandler;

class CheckMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CheckMiddleware
    {
        return new CheckMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(ErrorHandler::class),
            $container->get(CompanyService::class)
        );
    }
}