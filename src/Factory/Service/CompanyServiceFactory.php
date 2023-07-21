<?php

namespace Company\Factory\Service;

use Company\Repository\HiveRepositoryInterface;
use Company\Service\CompanyService;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Notification\Service\NotificationService;
use User\Service\AccountService;
use User\Service\RoleService;
use User\Service\UtilityService;

class CompanyServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CompanyService
    {
        $config = $container->get('config');
        $config = $config['company'] ?? [];

        return new CompanyService(
            $container->get(HiveRepositoryInterface::class),
            $container->get(AccountService::class),
            $container->get(RoleService::class),
            $container->get(NotificationService::class),
            $container->get(UtilityService::class),
            $config
        );
    }
}