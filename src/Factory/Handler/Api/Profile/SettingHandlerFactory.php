<?php

namespace Pi\Company\Factory\Handler\Api\Profile;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Handler\Api\Profile\SettingHandler;
use Pi\Company\Service\CompanyService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class SettingHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SettingHandler
    {
        return new SettingHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}