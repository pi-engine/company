<?php

namespace Pi\Company\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;
use Pi\Company\Model\Inventory;
use Pi\Company\Model\Member;
use Pi\Company\Model\Package;

interface CompanyRepositoryInterface
{
    public function getCompany(array $params = []): array|Inventory;

    public function addCompany(array $params = []): Inventory;

    public function updateCompany(int $companyId, array $params = []): void;

    public function getCompanyList($params = []): HydratingResultSet;

    public function getCompanyCount($params = []): int;

    public function getMember(array $params = []): array|Member;

    public function getMemberList($params = []): HydratingResultSet;

    public function getMemberListByCompany($params = []): HydratingResultSet;

    public function getMemberCount(array $params = []): int;

    public function addMember(array $params = []): Member;

    public function updateMember(int $memberId, array $params = []): void;

    public function setDefault(int $userId, int $companyId): void;

    public function getPackage(array $params = []): array|Package;

    public function getPackageList($params = []): HydratingResultSet;

    public function addPackage(array $params = []): Package;

    public function updatePackage(int $packageId, array $params = []): void;
}