<?php

namespace Pi\Company\Factory\Handler\Api\Package;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Handler\Api\Package\CurrentHandler;
use Pi\Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CurrentHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CurrentHandler
    {
        return new CurrentHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}