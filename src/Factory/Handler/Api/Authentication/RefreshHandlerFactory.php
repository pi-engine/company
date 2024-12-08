<?php

namespace Pi\Company\Factory\Handler\Api\Authentication;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Handler\Api\Authentication\RefreshHandler;
use Pi\Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class RefreshHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RefreshHandler
    {
        return new RefreshHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}
