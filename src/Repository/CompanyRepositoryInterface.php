<?php

namespace Company\Repository;

use Company\Model\Inventory;
use Company\Model\Member;
use Laminas\Db\ResultSet\HydratingResultSet;

interface CompanyRepositoryInterface
{
    public function getCompany(array $params = []): array|Inventory;

    public function addCompany(array $params = []): Inventory;

    public function updateCompany(int $companyId, array $params = []): void;

    public function getMember(array $params = []): array|Member;

    public function getMemberList($params = []): HydratingResultSet;

    public function getMemberListByCompany($params = []): HydratingResultSet;

    public function getMemberCount(array $params = []): int;

    public function addMember(array $params = []): Member;

    public function updateMember(int $memberId, array $params = []): void;

    public function setDefault(int $userId, int $companyId): void;
}