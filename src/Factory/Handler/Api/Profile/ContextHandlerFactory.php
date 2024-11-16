<?php

namespace Pi\Company\Factory\Handler\Api\Profile;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Handler\Api\Profile\ContextHandler;
use Pi\Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
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