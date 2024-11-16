<?php

namespace Company\Factory\Service;

use Company\Repository\CompanyRepositoryInterface;
use Company\Service\CompanyService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Notification\Service\NotificationService;
use Pi\Core\Service\CacheService;
use Pi\Core\Service\UtilityService;
use Pi\User\Service\AccountService;
use Pi\User\Service\RoleService;
use Psr\Container\ContainerInterface;

class CompanyServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CompanyService
    {
        $config = $container->get('config');
        $config = array_merge($config['global'], $config['company']);

        return new CompanyService(
            $container->get(CompanyRepositoryInterface::class),
            $container->get(AccountService::class),
            $container->get(RoleService::class),
            $container->get(CacheService::class),
            $container->get(NotificationService::class),
            $container->get(UtilityService::class),
            $config
        );
    }
}