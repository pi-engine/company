<?php

namespace Company\Factory\Repository;

use Company\Model\Inventory;
use Company\Model\Member;
use Company\Repository\HiveRepository;
use Interop\Container\Containerinterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CompanyRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): HiveRepository
    {
        return new HiveRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new Inventory('','','', 0,0,0,0, 0, 0, '','','','','','','','','', 0),
            new Member(0,0,0,0,0,'', '', '', '', 0)
        );
    }
}