<?php

namespace Pi\Company\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Hydrator\HydratorInterface;
use Pi\Company\Model\Inventory;
use Pi\Company\Model\Member;
use Pi\Company\Model\MemberCompany;
use Pi\Company\Model\Package;
use Pi\Company\Model\Team\TeamInventory;
use Pi\Company\Model\Team\TeamMember;
use RuntimeException;

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
     * Package Table name
     *
     * @var string
     */
    private string $tablePackage = 'company_package';

    /**
     * Team inventory Table name
     *
     * @var string
     */
    private string $tableTeamInventory = 'company_team_inventory';

    /**
     * Team member Table name
     *
     * @var string
     */
    private string $tableTeamMember = 'company_team_member';

    /**
     * Account Table name
     *
     * @var string
     */
    private string $tableAccount = 'user_account';
    private string $tableProfile = 'user_profile';

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $db;

    private Inventory $inventoryPrototype;

    private Member $memberPrototype;

    private MemberCompany $memberCompanyPrototype;

    private Package $packagePrototype;

    private TeamInventory $teamInventoryPrototype;

    private TeamMember $teamMemberPrototype;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    public function __construct(
        AdapterInterface  $db,
        HydratorInterface $hydrator,
        Inventory         $inventoryPrototype,
        Member            $memberPrototype,
        MemberCompany     $memberCompanyPrototype,
        Package           $packagePrototype,
        TeamInventory     $teamInventoryPrototype,
        TeamMember        $teamMemberPrototype
    ) {
        $this->db                     = $db;
        $this->hydrator               = $hydrator;
        $this->inventoryPrototype     = $inventoryPrototype;
        $this->memberPrototype        = $memberPrototype;
        $this->memberCompanyPrototype = $memberCompanyPrototype;
        $this->packagePrototype       = $packagePrototype;
        $this->teamInventoryPrototype = $teamInventoryPrototype;
        $this->teamMemberPrototype    = $teamMemberPrototype;
    }

    public function addCompany(array $params = []): Inventory
    {
        $insert = new Insert($this->tableInventory);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getCompany(['id' => $id]);
    }

    public function getCompany(array $params = []): array|Inventory
    {
        // Set
        $where = ['id' => (int)$params['id']];

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableInventory)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                'Failed retrieving row with identifier; unknown database error.',
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->inventoryPrototype);
        $resultSet->initialize($result);
        $company = $resultSet->current();

        if (!$company) {
            return [];
        }

        return $company;
    }

    public function getCompanyList($params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['title']) && !empty($params['title'])) {
            $where['company.title like ?'] = '%' . $params['title'] . '%';
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['company.state'] = $params['state'];
        }
        if (isset($params['reseller_id']) && !empty($params['reseller_id'])) {
            $where['company.reseller_id'] = $params['reseller_id'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $where['company.id'] = $params['id'];
        }

        $sql    = new Sql($this->db);
        $from   = ['company' => $this->tableInventory];
        $select = $sql->select()->from($from)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'company.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $select->join(
            ['package' => $this->tablePackage],
            'company.package_id=package.id',
            [
                'package_title' => 'title',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        if (isset($params['order']) && !empty($params['order'])) {
            $select->order($params['order']);
        }
        if (isset($params['offset']) && !empty($params['offset'])) {
            $select->offset($params['offset']);
        }
        if (isset($params['limit']) && !empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->inventoryPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getCompanyCount($params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];

        $where = [];
        if (isset($params['title']) && !empty($params['title'])) {
            $where['title like ?'] = '%' . $params['title'] . '%';
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['state'] = $params['state'];
        }
        if (isset($params['reseller_id']) && !empty($params['reseller_id'])) {
            $where['reseller_id'] = $params['reseller_id'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = $params['id'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableInventory)->columns($columns)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();

        return (int)$row['count'];
    }

    public function updateCompany(int $companyId, array $params = []): void
    {
        $update = new Update($this->tableInventory);
        $update->set($params);
        $update->where(['id' => $companyId]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function addMember(array $params = []): Member
    {
        $insert = new Insert($this->tableMember);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getMember(['id' => $id]);
    }

    public function getMember(array $params = []): array|Member
    {
        // Set
        $where = [];
        if (isset($params['id']) && !empty($params['id'])) {
            $where['member.id'] = (int)$params['id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['member.company_id'] = $params['company_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['member.user_id'] = $params['user_id'];
        }
        if (isset($params['status']) && (int)$params['status'] > 0) {
            $where['member.status'] = (int)$params['status'];
        }
        if (isset($params['is_default']) && (int)$params['is_default'] > 0) {
            $where['member.is_default'] = (int)$params['is_default'];
        }

        $limit = 1;
        $order = ['time_create DESC', 'id DESC'];

        $sql    = new Sql($this->db);
        $from   = ['member' => $this->tableMember];
        $select = $sql->select()->from($from)->where($where)->order($order)->limit($limit);
        $select->join(
            ['account' => $this->tableAccount],
            'member.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $select->join(
            ['profile' => $this->tableProfile],
            'member.user_id=profile.user_id',
            [
                'first_name',
                'last_name',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                'Failed retrieving row with identifier; unknown database error.',
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->memberPrototype);
        $resultSet->initialize($result);
        $member = $resultSet->current();

        if (!$member) {
            return [];
        }

        return $member;
    }

    public function getMemberList($params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['member.company_id'] = $params['company_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['member.user_id'] = $params['user_id'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }

        $sql    = new Sql($this->db);
        $from   = ['member' => $this->tableMember];
        $select = $sql->select()->from($from)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'member.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $select->join(
            ['profile' => $this->tableProfile],
            'member.user_id=profile.user_id',
            [
                'first_name',
                'last_name',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        if (isset($params['order']) && !empty($params['order'])) {
            $select->order($params['order']);
        }
        if (isset($params['offset']) && !empty($params['offset'])) {
            $select->offset($params['offset']);
        }
        if (isset($params['limit']) && !empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->memberPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getMemberListByCompany($params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['member.company_id'] = $params['company_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['member.user_id'] = $params['user_id'];
        }

        $sql    = new Sql($this->db);
        $from   = ['member' => $this->tableMember];
        $select = $sql->select()->from($from)->where($where);
        $select->join(
            ['company' => $this->tableInventory],
            'member.company_id=company.id',
            [
                'title',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        if (isset($params['order']) && !empty($params['order'])) {
            $select->order($params['order']);
        }
        if (isset($params['offset']) && !empty($params['offset'])) {
            $select->offset($params['offset']);
        }
        if (isset($params['limit']) && !empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->memberCompanyPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getMemberCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
        $where   = [];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['member.company_id'] = $params['company_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['member.user_id'] = $params['user_id'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }

        $sql    = new Sql($this->db);
        $from   = ['member' => $this->tableMember];
        $select = $sql->select()->from($from)->columns($columns)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'member.user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();

        return (int)$row['count'];
    }

    public function updateMember(int $memberId, array $params = []): void
    {
        $update = new Update($this->tableMember);
        $update->set($params);
        $update->where(['id' => $memberId]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function setDefault(int $userId, int $companyId): void
    {
        $update = new Update($this->tableMember);
        $update->set(['is_default' => new Expression(sprintf('CASE WHEN company_id = %s THEN 1 ELSE 0 END', $companyId))]);
        $update->where(['user_id' => $userId]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function addPackage(array $params = []): Package
    {
        $insert = new Insert($this->tablePackage);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getPackage(['id' => $id]);
    }

    public function getPackage(array $params = []): array|Package
    {
        // Set
        $where = ['id' => (int)$params['id']];

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tablePackage)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                'Failed retrieving row with identifier; unknown database error.',
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->packagePrototype);
        $resultSet->initialize($result);
        $package = $resultSet->current();

        if (!$package) {
            return [];
        }

        return $package;
    }

    public function getPackageList($params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['state']) && !empty($params['state'])) {
            $where['state'] = $params['state'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = $params['id'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tablePackage)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->packagePrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function updatePackage(int $packageId, array $params = []): void
    {
        $update = new Update($this->tablePackage);
        $update->set($params);
        $update->where(['id' => $packageId]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function addTeam(array $params = []): TeamInventory
    {
        $insert = new Insert($this->tableTeamInventory);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getTeam(['id' => $id]);
    }

    public function getTeam(array $params = []): array|TeamInventory
    {
        // Set
        $where = ['id' => (int)$params['id']];

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableTeamInventory)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                'Failed retrieving row with identifier; unknown database error.',
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->teamInventoryPrototype);
        $resultSet->initialize($result);
        $team = $resultSet->current();

        if (!$team) {
            return [];
        }

        return $team;
    }

    public function getTeamList($params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['company_id'] = $params['company_id'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = $params['id'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableTeamInventory)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->teamInventoryPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function updateTeam(int $teamId, array $params = []): void
    {
        $update = new Update($this->tableTeamInventory);
        $update->set($params);
        $update->where(['id' => $teamId]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function addTeamMember(array $params = []): TeamMember
    {
        $insert = new Insert($this->tableTeamMember);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getTeamMember(['id' => $id]);
    }

    public function getTeamMember(array $params = []): array|TeamMember
    {
        // Set
        $where = [];
        if (isset($params['id']) && !empty($params['id'])) {
            $where['member.id'] = (int)$params['id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['member.company_id'] = $params['company_id'];
        }
        if (isset($params['team_id']) && !empty($params['team_id'])) {
            $where['member.team_id'] = $params['team_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['member.user_id'] = $params['user_id'];
        }
        if (isset($params['status']) && (int)$params['status'] > 0) {
            $where['member.status'] = (int)$params['status'];
        }

        $limit = 1;
        $order = ['time_create DESC', 'id DESC'];

        $sql    = new Sql($this->db);
        $from   = ['member' => $this->tableTeamMember];
        $select = $sql->select()->from($from)->where($where)->order($order)->limit($limit);
        $select->join(
            ['account' => $this->tableAccount],
            'member.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $select->join(
            ['profile' => $this->tableProfile],
            'member.user_id=profile.user_id',
            [
                'first_name',
                'last_name',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $select->join(
            ['team' => $this->tableTeamInventory],
            'member.team_id=team.id',
            [
                'team_title' => 'title',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                'Failed retrieving row with identifier; unknown database error.',
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->teamMemberPrototype);
        $resultSet->initialize($result);
        $member = $resultSet->current();

        if (!$member) {
            return [];
        }

        return $member;
    }

    public function getTeamMemberList($params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['member.company_id'] = $params['company_id'];
        }
        if (isset($params['team_id']) && !empty($params['team_id'])) {
            $where['member.team_id'] = $params['team_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['member.user_id'] = $params['user_id'];
        }
        if (isset($params['status']) && (int)$params['status'] > 0) {
            $where['member.status'] = (int)$params['status'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }

        $sql    = new Sql($this->db);
        $from   = ['member' => $this->tableTeamMember];
        $select = $sql->select()->from($from)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'member.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $select->join(
            ['profile' => $this->tableProfile],
            'member.user_id=profile.user_id',
            [
                'first_name',
                'last_name',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $select->join(
            ['team' => $this->tableTeamInventory],
            'member.team_id=team.id',
            [
                'team_title' => 'title',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        if (isset($params['order']) && !empty($params['order'])) {
            $select->order($params['order']);
        }
        if (isset($params['offset']) && !empty($params['offset'])) {
            $select->offset($params['offset']);
        }
        if (isset($params['limit']) && !empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->teamMemberPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getTeamMemberCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
        $where   = [];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['member.company_id'] = $params['company_id'];
        }
        if (isset($params['team_id']) && !empty($params['team_id'])) {
            $where['member.team_id'] = $params['team_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['member.user_id'] = $params['user_id'];
        }
        if (isset($params['status']) && (int)$params['status'] > 0) {
            $where['member.status'] = (int)$params['status'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }

        $sql    = new Sql($this->db);
        $from   = ['member' => $this->tableTeamMember];
        $select = $sql->select()->from($from)->columns($columns)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'member.user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();

        return (int)$row['count'];
    }

    public function updateTeamMember(int $memberId, array $params = []): void
    {
        $update = new Update($this->tableTeamMember);
        $update->set($params);
        $update->where(['id' => $memberId]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function deleteTeamMember(int $memberId): void
    {
        $delete = new Delete($this->tableTeamMember);
        $delete->where(['id' => $memberId]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }
}