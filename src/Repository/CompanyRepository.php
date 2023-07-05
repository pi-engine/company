<?php

namespace Company\Repository;

use Company\Model\Inventory;
use Company\Model\Member;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\HydratorInterface;

class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * Inventory Table name
     *
     * @var string
     */
    private string $tableInventory = 'company_inventory';

    /**
     * Member Table name
     *
     * @var string
     */
    private string $tableMember = 'company_member';

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $db;


    private Inventory $inventoryPrototype;

    private Member $memberPrototype;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Inventory $inventoryPrototype,
        Member $memberPrototype
    ) {
        $this->db                 = $db;
        $this->hydrator           = $hydrator;
        $this->inventoryPrototype = $inventoryPrototype;
        $this->memberPrototype    = $memberPrototype;
    }
}