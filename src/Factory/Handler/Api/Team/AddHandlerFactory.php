<?php

namespace Pi\Company\Factory\Handler\Api\Team;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Handler\Api\Team\AddHandler;
use Pi\Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class AddHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AddHandler
    {
        return new AddHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}