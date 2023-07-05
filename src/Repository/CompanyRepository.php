<?php

namespace Company\Repository;

use Company\Model\Inventory;
use Company\Model\Member;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Hydrator\HydratorInterface;
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
     * Account Table name
     *
     * @var string
     */
    private string $tableAccount = 'user_account';

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

    public function getCompany(array $params = []): array|Inventory
    {
        // Set
        $where = [            'id' => (int)$params['id']        ];

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

    public function getMember(array $params = []): array|Member
    {
        // Set
        $where = [];
        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = (int)$params['id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['company_id'] = $params['company_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        if (isset($params['status']) && (int)$params['status'] > 0) {
            $where['status'] = (int)$params['status'];
        }

        $limit = 1;
        $order = ['time_create DESC', 'id DESC'];

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableMember)->where($where)->order($order)->limit($limit);
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
        $select = $sql->select()->from($from)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);
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

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->memberPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getMemberCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
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

        $sql       = new Sql($this->db);
        $from   = ['member' => $this->tableMember];
        $select = $sql->select()->from($from)->columns($columns)->where($where);
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
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();

        return (int)$row['count'];
    }
}