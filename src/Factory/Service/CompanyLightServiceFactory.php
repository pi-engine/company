<?php

namespace Pi\Company\Factory\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Repository\CompanyRepositoryInterface;
use Pi\Company\Service\CompanyLightService;
use Psr\Container\ContainerInterface;

class CompanyLightServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CompanyLightService
    {
        $config = $container->get('config');
        $config = array_merge($config['global'], $config['company']);

        return new CompanyLightService(
            $container->get(CompanyRepositoryInterface::class),
            $config
        );
    }
}