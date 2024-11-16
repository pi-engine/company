<?php

namespace Pi\Company\Factory\Handler\Api\Member;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Handler\Api\Member\RoleHandler;
use Pi\Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class RoleHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RoleHandler
    {
        return new RoleHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}