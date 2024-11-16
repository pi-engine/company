<?php

namespace Pi\Company\Factory\Handler\Admin;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Handler\Admin\PackageUpdateHandler;
use Pi\Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class PackageUpdateHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PackageUpdateHandler
    {
        return new PackageUpdateHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}