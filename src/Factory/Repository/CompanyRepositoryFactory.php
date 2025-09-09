<?php

namespace Pi\Company\Factory\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Company\Model\Inventory;
use Pi\Company\Model\Member;
use Pi\Company\Model\MemberCompany;
use Pi\Company\Model\Package;
use Pi\Company\Model\Team\TeamInventory;
use Pi\Company\Model\Team\TeamMember;
use Pi\Company\Repository\CompanyRepository;
use Psr\Container\ContainerInterface;

class CompanyRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CompanyRepository
    {
        return new CompanyRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new Inventory('', '', '', 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', 0),
            new Member(0, 0, 0, 0, 0, 0, '', '', '', '', 0),
            new MemberCompany(0, 0, 0, 0, 0, 0, '', 0),
            new Package('', 0, '', 0),
            new TeamInventory('', 0, 0, '', 0),
            new TeamMember(0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', 0)
        );
    }
}