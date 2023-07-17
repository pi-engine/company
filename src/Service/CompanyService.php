<?php

namespace Company\Service;

use Company\Repository\CompanyRepositoryInterface;
use Notification\Service\NotificationService;
use User\Service\AccountService;
use User\Service\RoleService;
use User\Service\UtilityService;

class CompanyService implements ServiceInterface
{
    /** @var AccountService */
    protected AccountService $accountService;

    /* @var RoleService */
    protected RoleService $roleService;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /** @var NotificationService */
    protected NotificationService $notificationService;

    /** @var CompanyRepositoryInterface */
    protected CompanyRepositoryInterface $companyRepository;

    /* @var array */
    protected array $config;

    protected string $companyAdminRole  = 'companyadmin';
    protected string $companyMemberRole = 'companymember';

    protected array $profileFields
        = [
            'title',
            'text_description',
            'industry_id',
            'address_1',
            'address_2',
            'country',
            'state',
            'city',
            'zip_code',
            'phone',
            'website',
            'email',
        ];

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        AccountService $accountService,
        RoleService $roleService,
        NotificationService $notificationService,
        UtilityService $utilityService,
        $config
    ) {
        $this->companyRepository   = $companyRepository;
        $this->accountService      = $accountService;
        $this->roleService         = $roleService;
        $this->notificationService = $notificationService;
        $this->utilityService      = $utilityService;
        $this->config              = $config;
    }

    public function check($account, $params): array
    {
        // Set result
        $result = [
            'result' => true,
            'data'   => [
                'user_id'    => $account['id'],
                'company_id' => 0,
                'user'       => $account,
            ],
            'error'  => [],
        ];

        // Check user id
        if (!$result['data']['user_id'] || $result['data']['user_id'] == 0) {
            return [
                'result' => false,
                'data'   => [],
                'error'  => [
                    'code'    => 1,
                    'message' => 'No user id is selected',
                ],
            ];
        }

        $result['data']['member'] = $this->getMember($result['data']['user_id'], $params);

        // Check member and register if not found
        if (empty($result['data']['member'])) {
            $result['data']['member'] = $this->registerCompany($account);
        }

        // Check user
        if (empty($result['data']['member']['status']) || $result['data']['member']['status'] != 1) {
            return [
                'result' => false,
                'data'   => [],
                'error'  => [
                    'code'    => 4,
                    'message' => 'You account is inactive by admin',
                ],
            ];
        }

        // Get company info
        $result['data']['company']    = $this->getCompany($result['data']['member']['company_id']);
        $result['data']['company_id'] = $result['data']['member']['company_id'];

        // Check
        if (empty($result['data']['company']) || $result['data']['company']['status'] != 1) {
            return [
                'result' => false,
                'data'   => [],
                'error'  => [
                    'code'    => 5,
                    'message' => 'No company found for selected user',
                ],
            ];
        }

        // Get cached user
        $cacheUser = $this->accountService->getUserFromCache($account['id']);

        // Get user roles
        $result['data']['roles'] = $cacheUser['roles'];

        // Check company
        if (!in_array('companyadmin', $result['data']['roles'])) {
            if (!in_array('companymember', $result['data']['roles'])) {
                // Set roles
                if ((int)$result['data']['member']['user_id'] == (int)$result['data']['company']['user_id']) {
                    $this->roleService->addRoleAccount((int)$account['id'], $this->companyAdminRole);
                } else {
                    $this->roleService->addRoleAccount((int)$account['id'], $this->companyMemberRole);
                }

                // Get cached user
                $cacheUser = $this->accountService->getUserFromCache($account['id']);

                // Get user roles
                $result['data']['roles'] = $cacheUser['roles'];
            }
        }

        return $result;
    }

    public function registerCompany($account): array
    {
        // Set company params
        $addParams = [
            'title'            => $account['company'] ?? sprintf('%s company', $account['name']),
            'user_id'          => $account['id'],
            'time_create'      => time(),
            'time_update'      => time(),
            'phone'            => $account['mobile'] ?? null,
            'email'            => $account['email'] ?? null,
            'status'           => 1,
            'industry_id'      => $account['industry_id'] ?? 0,
            'text_description' => '',
            'setting'          => json_encode([]),
        ];

        // Add company
        $company = $this->companyRepository->addCompany($addParams);
        $company = $this->canonizeCompany($company);

        // Set member params
        $addParams = [
            'company_id'  => $company['id'],
            'user_id'     => $account['id'],
            'time_create' => time(),
            'time_update' => time(),
            'status'      => 1,
        ];

        // Add member
        $member = $this->companyRepository->addMember($addParams);
        $member = $this->canonizeMember($member);

        // Add role
        $this->roleService->addRoleAccount((int)$account['id'], $this->companyAdminRole);

        // Send notification
        // Todo: add it

        return $member;
    }

    public function getCompany(int $companyId): array
    {
        $where  = ['id' => $companyId];
        $member = $this->companyRepository->getCompany($where);
        return $this->canonizeCompany($member);
    }

    public function updateCompany($authentication, $requestBody): array
    {
        $profileParams = [];
        foreach ($requestBody as $key => $value) {
            if (in_array($key, $this->profileFields)) {
                if (empty($value)) {
                    $profileParams[$key] = null;
                } elseif (is_string($value)) {
                    $profileParams[$key] = $value;
                }
            }
        }

        // Set time update
        $profileParams['time_update'] = time();

        // Update company
        $this->companyRepository->updateCompany((int)$authentication['company_id'], $profileParams);

        // Set result
        return [
            'result' => true,
            'data'   => [
                'message' => 'Company profile updated successfully !',
            ],
            'error'  => [],
        ];
    }

    public function getMember(int $userId, $params): array
    {
        // Set where
        $where = ['user_id' => $userId];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['company_id'] = $params['company_id'];
        }

        $member = $this->companyRepository->getMember($where);
        return $this->canonizeMember($member);
    }

    public function getMemberList($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['time_create DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
        ];

        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $listParams['company_id'] = $params['company_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }

        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getMemberList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeMember($row);
        }

        // Get count
        $count = $this->companyRepository->getMemberCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => [],
        ];
    }

    public function addMember($params): array
    {
        // Add or Get account
        $account = $this->accountService->addOrGetAccount($params);

        // Check member not exist
        $member = $this->getMember($account['id'], ['company_id' => $params['company_id']]);

        // Add member if not exist
        if (!empty($member)) {
            return [
                'result' => false,
                'data'   => $member,
                'error'  => [
                    'message' => 'This member added before in your company !'
                ],
            ];
        }

        // Set member params
        $memberParams = [
            'company_id'  => $params['company_id'],
            'user_id'     => $account['id'],
            'time_create' => time(),
            'time_update' => time(),
            'status'      => 1,
        ];

        // Add member
        $member = $this->companyRepository->addMember($memberParams);
        $member = $this->canonizeMember($member);

        // Add role
        $this->roleService->addRoleAccount((int)$account['id'], $params['role'] ?? $this->companyMemberRole);

        return [
            'result' => true,
            'data'   => $member,
            'error'  => [],
        ];
    }

    public function canonizeCompany($company): array
    {
        if (empty($company)) {
            return [];
        }

        if (is_object($company)) {
            $company = [
                'id'               => $company->getId(),
                'title'            => $company->getTitle(),
                'setting'          => $company->getSetting(),
                'text_description' => $company->getTextDescription(),
                'user_id'          => $company->getUserId(),
                'reseller_id'      => $company->getResellerId(),
                'industry_id'      => $company->getIndustryId(),
                'time_create'      => $company->getTimeCreate(),
                'time_update'      => $company->getTimeUpdate(),
                'status'           => $company->getStatus(),
                'address_1'        => $company->getAddress1(),
                'address_2'        => $company->getAddress2(),
                'country'          => $company->getCountry(),
                'state'            => $company->getState(),
                'city'             => $company->getCity(),
                'zip_code'         => $company->getZipCode(),
                'phone'            => $company->getPhone(),
                'website'          => $company->getWebsite(),
                'email'            => $company->getEmail(),
            ];
        } else {
            $company = [
                'id'               => $company['id'],
                'title'            => $company['title'],
                'setting'          => $company['setting'],
                'text_description' => $company['text_description'],
                'user_id'          => $company['user_id'],
                'reseller_id'      => $company['reseller_id'],
                'time_create'      => $company['time_create'],
                'time_update'      => $company['time_update'],
                'status'           => $company['status'],
                'address_1'        => $company['address_1'],
                'address_2'        => $company['address_2'],
                'country'          => $company['country'],
                'state'            => $company['state'],
                'city'             => $company['city'],
                'zip_code'         => $company['zip_code'],
                'phone'            => $company['phone'],
                'website'          => $company['website'],
                'email'            => $company['email'],
            ];
        }

        $company['setting'] = json_decode($company['setting'], true);

        return $company;
    }

    public function canonizeMember($member): array
    {
        if (empty($member)) {
            return [];
        }

        if (is_object($member)) {
            $member = [
                'id'            => $member->getId(),
                'company_id'    => $member->getCompanyId(),
                'user_id'       => $member->getUserId(),
                'time_create'   => $member->getTimeCreate(),
                'time_update'   => $member->getTimeUpdate(),
                'status'        => $member->getStatus(),
                'user_identity' => $member->getUserIdentity(),
                'user_name'     => $member->getUserName(),
                'user_email'    => $member->getUserEmail(),
                'user_mobile'   => $member->getUserMobile(),
            ];
        } else {
            $member = [
                'id'            => $member['id'],
                'company_id'    => $member['company_id'],
                'user_id'       => $member['user_id'],
                'time_create'   => $member['time_create'],
                'time_update'   => $member['time_update'],
                'status'        => $member['status'],
                'user_identity' => $member['user_identity'],
                'user_name'     => $member['user_name'],
                'user_email'    => $member['user_email'],
                'user_mobile'   => $member['user_mobile'],
            ];
        }

        return $member;
    }
}