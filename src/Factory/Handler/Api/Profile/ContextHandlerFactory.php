<?php

namespace Company\Factory\Handler\Api\Profile;

use Company\Handler\Api\Profile\ContextHandler;
use Company\Service\CompanyService;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ContextHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ContextHandler
    {
        return new ContextHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}