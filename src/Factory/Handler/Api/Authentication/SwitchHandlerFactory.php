<?php

namespace Company\Factory\Handler\Api\Authentication;

use Company\Handler\Api\Authentication\SwitchHandler;
use Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class SwitchHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SwitchHandler
    {
        return new SwitchHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}
