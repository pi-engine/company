<?php

namespace Pi\Company\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;
use Pi\Company\Model\Inventory;
use Pi\Company\Model\Member;
use Pi\Company\Model\Package;
use Pi\Company\Model\Team\TeamInventory;
use Pi\Company\Model\Team\TeamMember;

interface CompanyRepositoryInterface
{
    public function addCompany(array $params = []): Inventory;

    public function getCompany(array $params = []): array|Inventory;

    public function getCompanyList($params = []): HydratingResultSet;

    public function getCompanyCount($params = []): int;

    public function updateCompany(int $companyId, array $params = []): void;

    public function addMember(array $params = []): Member;

    public function getMember(array $params = []): array|Member;

    public function getMemberList($params = []): HydratingResultSet;

    public function getMemberListByCompany($params = []): HydratingResultSet;

    public function getMemberCount(array $params = []): int;

    public function updateMember(int $memberId, array $params = []): void;

    public function setDefault(int $userId, int $companyId): void;

    public function addPackage(array $params = []): Package;

    public function getPackage(array $params = []): array|Package;

    public function getPackageList($params = []): HydratingResultSet;

    public function updatePackage(int $packageId, array $params = []): void;

    public function addTeam(array $params = []): TeamInventory;

    public function getTeam(array $params = []): array|TeamInventory;

    public function getTeamList($params = []): HydratingResultSet;

    public function updateTeam(int $teamId, array $params = []): void;

    public function addTeamMember(array $params = []): TeamMember;

    public function getTeamMember(array $params = []): array|TeamMember;

    public function getTeamMemberList($params = []): HydratingResultSet;

    public function getTeamMemberCount(array $params = []): int;

    public function updateTeamMember(int $memberId, array $params = []): void;

    public function deleteTeamMember(int $memberId): void;
}