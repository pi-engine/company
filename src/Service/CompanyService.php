<?php

namespace Company\Service;

use Company\Repository\CompanyRepositoryInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Math\Rand;
use Notification\Service\NotificationService;
use User\Service\AccountService;
use User\Service\CacheService;
use User\Service\RoleService;
use User\Service\UtilityService;

class CompanyService implements ServiceInterface
{
    /** @var CompanyRepositoryInterface */
    protected CompanyRepositoryInterface $companyRepository;

    /** @var AccountService */
    protected AccountService $accountService;

    /* @var RoleService */
    protected RoleService $roleService;

    /* @var CacheService */
    protected CacheService $cacheService;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /** @var NotificationService */
    protected NotificationService $notificationService;

    /* @var array */
    protected array $config;

    public string $companyViewerRole   = 'companyviewer';
    public string $companyMemberRole   = 'companymember';
    public string $companyExternalRole = 'companyexternal';
    public string $companyAuditRole    = 'companyaudit';
    public string $companyManagerRole  = 'companymanager';
    public string $companyAdminRole    = 'companyadmin';
    public int    $industryId          = 1;
    public int    $packageId           = 1;
    public string $packageExpire       = '+2 weeks';
    public array  $wizardSteps
                                       = [
            'user_profile'    => false,
            'company_profile' => false,
            'voucher'         => false,
        ];

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
        CacheService $cacheService,
        NotificationService $notificationService,
        UtilityService $utilityService,
        $config
    ) {
        $this->companyRepository   = $companyRepository;
        $this->accountService      = $accountService;
        $this->roleService         = $roleService;
        $this->cacheService        = $cacheService;
        $this->notificationService = $notificationService;
        $this->utilityService      = $utilityService;
        $this->config              = $config;
    }

    public function authorization($account, $params): array
    {
        // Set result
        $result = [
            'result' => true,
            'data'   => [
                'user_id'    => $account['id'],
                'company_id' => 0,
                'package_id' => 0,
                'project_id' => 0,
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
                    'message' => 'No user id is selected',
                ],
                'status' => StatusCodeInterface::STATUS_UNAUTHORIZED,
            ];
        }

        // Set member params
        $memberParams = [
            'is_default' => 1,
        ];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $memberParams['company_id'] = $params['company_id'];
        }

        // Get member
        $result['data']['member'] = $this->getMember($result['data']['user_id'], $memberParams);

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
                    'message' => 'You account is inactive by admin',
                ],
                'status' => StatusCodeInterface::STATUS_UNAUTHORIZED,
            ];
        }

        // Get company info
        $result['data']['company']    = $this->getCompany($result['data']['member']['company_id']);
        $result['data']['company_id'] = $result['data']['company']['id'];
        $result['data']['package_id'] = $result['data']['company']['package_id'];

        // Check
        if (empty($result['data']['company']) || $result['data']['company']['status'] != 1) {
            return [
                'result' => false,
                'data'   => [],
                'error'  => [
                    'message' => 'No company found for selected user',
                ],
                'status' => StatusCodeInterface::STATUS_UNAUTHORIZED,
            ];
        }

        // Get cached user
        $cacheUser = $this->accountService->getUserFromCache($account['id']);

        // Get user roles
        $result['data']['roles'] = $cacheUser['roles'];

        // Check company
        if (!in_array($this->companyAdminRole, $result['data']['roles'])) {
            if (!in_array($this->companyMemberRole, $result['data']['roles'])) {
                // Set roles
                if ((int)$result['data']['member']['user_id'] == (int)$result['data']['company']['user_id']) {
                    $this->roleService->addRoleAccount($account, $this->companyAdminRole);
                } else {
                    $this->roleService->addRoleAccount($account, $this->companyMemberRole);
                }

                // Get cached user
                $cacheUser = $this->accountService->getUserFromCache($account['id']);

                // Get user roles
                $result['data']['roles'] = $cacheUser['roles'];
            }
        }


        // Check admin access
        $result['data']['is_admin'] = 0;
        if (in_array($this->companyAdminRole, $result['data']['roles'])
            || in_array($this->companyManagerRole, $result['data']['roles'])
        ) {
            $result['data']['is_admin'] = 1;
        }

        // Update user cache
        $this->cacheService->setUser($account['id'], ['authorization' => $result['data']]);

        // Set company cache
        $this->cacheService->setItem(sprintf('company-%s', $result['data']['company_id']), $result['data']['company']);

        return $result;
    }

    public function registerCompany($account): array
    {
        // Set company params
        $addParams = [
            'title'            => $account['company'] ?? sprintf('%s company', Rand::getString('8', 'abcdefghijklmnopqrstuvwxyz0123456789')),
            'user_id'          => $account['id'],
            'time_create'      => time(),
            'time_update'      => time(),
            'phone'            => $account['mobile'] ?? null,
            'email'            => $account['email'] ?? null,
            'status'           => 1,
            'industry_id'      => $this->industryId,
            'package_id'       => $this->packageId,
            'text_description' => '',
            'setting'          => json_encode([
                'general' => [],
                'context' => [],
                'wizard'  => [
                    'is_completed' => false,
                    'time_start'   => time(),
                    'time_end'     => 0,
                    'steps'        => $this->wizardSteps,
                ],
                'package' => [
                    'time_start'  => time(),
                    'time_renew'  => time(),
                    'time_expire' => strtotime($this->packageExpire),
                    'renew_count' => 1,
                ],
            ]),
        ];

        // Add company
        $company = $this->addCompany($addParams);

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
        $this->roleService->addRoleAccount($account, $this->companyAdminRole);

        // Send notification
        // Todo: add it

        return $member;
    }

    public function addCompany($params): array
    {
        $company = $this->companyRepository->addCompany($params);
        $company = $this->canonizeCompany($company);

        // Set company cache
        $this->cacheService->setItem(sprintf('company-%s', $company['id']), $company);

        return $company;
    }

    public function getCompany(int $companyId): array
    {
        $where   = ['id' => $companyId];
        $company = $this->companyRepository->getCompany($where);
        return $this->canonizeCompany($company);
    }

    public function updateCompany($authorization, $params): array
    {
        $profileParams = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $this->profileFields)) {
                if (is_numeric($value)) {
                    $profileParams[$key] = (int)$value;
                } elseif (is_string($value)) {
                    $profileParams[$key] = $value;
                } elseif (empty($value)) {
                    $profileParams[$key] = null;
                }
            }
        }

        // Set time update
        $profileParams['time_update'] = time();

        // Update company
        $this->companyRepository->updateCompany((int)$authorization['company_id'], $profileParams);

        // Set company cache
        $company = $this->getCompany((int)$authorization['company_id']);
        $this->cacheService->setItem(sprintf('company-%s', (int)$company['id']), $company);

        // Set result
        return [
            'result' => true,
            'data'   => [
                'message' => 'Company profile updated successfully !',
            ],
            'error'  => [],
        ];
    }

    public function updateCompanySetting($authorization, $params): array
    {
        // Set type
        $type = $params['type'];
        unset($params['type']);

        // Set context
        $setting = $authorization['company']['setting'] ?? [];

        // Set default params
        $setting['general'] = $setting['general'] ?? [];
        $setting['context'] = $setting['context'] ?? [];
        $setting['wizard']  = $setting['wizard'] ?? [];
        $setting['package'] = $setting['package'] ?? [
            'time_start'  => time(),
            'time_renew'  => time(),
            'time_expire' => strtotime($this->packageExpire),
            'renew_count' => 1,
        ];

        switch ($type) {
            case 'wizard':

                // Update data
                foreach ($params as $key => $value) {
                    if (in_array($key, array_keys($this->wizardSteps)) && in_array($value, ['true', 'false'])) {
                        $setting['wizard']['steps'][$key] = (bool)$value;
                    }
                }

                // update wizard
                if (isset($setting['wizard']['steps'])
                    && is_array($setting['wizard']['steps'])
                    && !empty($setting['wizard']['steps'])
                    && $setting['wizard']['is_completed'] === false
                ) {
                    $totalTrue = 0;
                    foreach ($setting['wizard']['steps'] as $step) {
                        if ($step === true) {
                            $totalTrue = $totalTrue + 1;
                        }
                    }

                    // Update data
                    if (count($setting['wizard']['steps']) === $totalTrue) {
                        $setting['wizard']['is_completed'] = true;
                        $setting['wizard']['time_end']     = time();
                    } else {
                        $setting['wizard']['is_completed'] = false;
                        $setting['wizard']['time_end']     = 0;
                    }
                }
                break;

            case 'general':
            case 'context':
                //case 'package':
            default:
                // Update data
                foreach ($params as $key => $value) {
                    $setting[$type][$key] = $value;
                }
                break;
        }

        // Set update update
        $profileParams
            = [
            'time_update' => time(),
            'setting'     => json_encode($setting, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];

        // Update company
        $this->companyRepository->updateCompany((int)$authorization['company_id'], $profileParams);

        // Set company cache
        $company = $this->getCompany((int)$authorization['company_id']);
        $this->cacheService->setItem(sprintf('company-%s', (int)$company['id']), $company);

        // Set result
        return [
            'result' => true,
            'data'   => [
                'message' => 'Company data updated successfully !',
                'company' => $company,
            ],
            'error'  => [],
        ];
    }

    public function getCompanyListByUser(int $userId): array
    {
        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getMemberListByCompany(['user_id' => $userId]);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeMemberCompany($row);
        }

        return $list;
    }

    public function getUserIdListByCompany(int $companyId): array
    {
        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getMemberListByCompany(['company_id' => $companyId]);
        foreach ($rowSet as $row) {
            $member = $this->canonizeMemberCompany($row);

            $list[$member['user_id']] = $member['user_id'];
        }

        return array_values($list);
    }

    public function getMember(int $userId, $params = []): array
    {
        // Set where
        $where = ['user_id' => $userId];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['company_id'] = $params['company_id'];
        }
        if (isset($params['is_default']) && !empty($params['is_default'])) {
            $where['is_default'] = $params['is_default'];
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
            $list[$row->getUserId()] = $this->canonizeMember($row);
        }

        // Get roles
        $roleList = $this->roleService->getRoleAccountList(array_keys($list), 'api');
        foreach ($roleList as $key => $roleUser) {
            $list[$key]['roles'] = $roleUser;
        }

        // Get count
        $count = $this->companyRepository->getMemberCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => array_values($list),
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
                    'message' => 'This member added before in your company !',
                ],
                'status' => StatusCodeInterface::STATUS_FORBIDDEN,
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

        // Add roles
        $roles = $params['roles'] ?? [$this->companyMemberRole];
        foreach ($roles as $role) {
            $this->roleService->addRoleAccount($account, $role);
        }

        return [
            'result' => true,
            'data'   => $member,
            'error'  => [],
        ];
    }

    public function updateMember($authorization, $params, $member): array
    {
        // Get member
        $member = $member ?? $this->getMember($params['user_id'], ['company_id' => $authorization['company_id']]);

        // Update account
        $account = ['id' => $params['user_id']];
        $account = $this->accountService->updateAccount($params, $account);

        // Set update params
        $updateParams = [
            'time_update' => time(),
            'status'      => $params['status'] ?? $member['status'],
        ];

        // Update member
        $this->companyRepository->updateMember((int)$member['id'], $updateParams);

        // Manage role
        if (isset($params['roles']) && !empty($params['roles'])) {
            $this->accountService->updateAccountRoles($params['roles'], $account, 'api', $authorization['member']);
        }

        return $account;
    }

    public function switchCompany(int $userId, int $companyId): array
    {
        // Make all user company list
        $companyList = [];
        $list        = $this->getCompanyListByUser($userId);
        foreach ($list as $single) {
            $companyList[] = $single['company_id'];
        }

        // Check user have access to select company
        if (!in_array($companyId, $companyList)) {
            return [
                'result' => false,
                'data'   => [],
                'error'  => [
                    'message' => 'Please select a company !',
                ],
            ];
        }

        // set selected company as default
        $this->companyRepository->setDefault($userId, $companyId);

        // Set result
        return [
            'result' => true,
            'data'   => [
                'message' => 'Your company has been changed successfully !',
            ],
            'error'  => [],
        ];
    }

    public function getPackage(int $packageId): array
    {
        $where   = ['id' => $packageId];
        $package = $this->companyRepository->getPackage($where);
        return $this->canonizePackage($package);
    }

    public function getPackageList($params): array
    {
        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getPackageList($params);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizePackage($row);
        }

        return $list;
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
                'package_id'       => $company->getPackageId(),
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
                'package_id'       => $company['package_id'],
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

        $company['setting']          = !empty($company['setting']) ? json_decode($company['setting'], true) : [];
        $company['hash']             = hash('sha256', sprintf('%s-%s', $company['id'], $company['time_create']));
        $company['slug']             = hash('md5', $company['id']);
        $company['is_company_setup'] = $company['setting']['wizard']['is_completed'] ?? false;

        if (isset($company['setting']['package']) && !empty($company['setting']['package'])) {
            $company['setting']['package']['time_start_view']  = $this->utilityService->date($company['setting']['package']['time_start']);
            $company['setting']['package']['time_renew_view']  = $this->utilityService->date($company['setting']['package']['time_renew']);
            $company['setting']['package']['time_expire_view'] = $this->utilityService->date($company['setting']['package']['time_expire']);
        }

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
                'is_default'    => $member->getIsDefault(),
                'user_identity' => $member->getUserIdentity(),
                'user_name'     => $member->getUserName(),
                'user_email'    => $member->getUserEmail(),
                'user_mobile'   => $member->getUserMobile(),
                'first_name'    => $member->getFirstName(),
                'last_name'     => $member->getLastName(),
            ];
        } else {
            $member = [
                'id'            => $member['id'],
                'company_id'    => $member['company_id'],
                'user_id'       => $member['user_id'],
                'time_create'   => $member['time_create'],
                'time_update'   => $member['time_update'],
                'status'        => $member['status'],
                'is_default'    => $member['is_default'],
                'user_identity' => $member['user_identity'],
                'user_name'     => $member['user_name'],
                'user_email'    => $member['user_email'],
                'user_mobile'   => $member['user_mobile'],
                'first_name'    => $member['first_name'],
                'last_name'     => $member['last_name'],
            ];
        }

        // Set role array
        $member['roles']                  = null;
        $member['roles_responsibilities'] = null;

        // Set time view
        $member['time_create_view'] = $this->utilityService->date($member['time_create']);
        $member['time_update_view'] = $this->utilityService->date($member['time_update']);

        return $member;
    }

    public function canonizeMemberCompany($member): array
    {
        if (empty($member)) {
            return [];
        }

        if (is_object($member)) {
            $member = [
                'id'          => $member->getId(),
                'company_id'  => $member->getCompanyId(),
                'user_id'     => $member->getUserId(),
                'time_create' => $member->getTimeCreate(),
                'time_update' => $member->getTimeUpdate(),
                'status'      => $member->getStatus(),
                'is_default'  => $member->getIsDefault(),
                'title'       => $member->getTitle(),
            ];
        } else {
            $member = [
                'id'          => $member['id'],
                'company_id'  => $member['company_id'],
                'user_id'     => $member['user_id'],
                'time_create' => $member['time_create'],
                'time_update' => $member['time_update'],
                'status'      => $member['status'],
                'is_default'  => $member['is_default'],
                'title'       => $member['title'],
            ];
        }

        // Set time view
        $member['time_create_view'] = $this->utilityService->date($member['time_create']);
        $member['time_update_view'] = $this->utilityService->date($member['time_update']);

        return $member;
    }

    public function canonizePackage($package): array
    {
        if (empty($package)) {
            return [];
        }

        if (is_object($package)) {
            $package = [
                'id'          => $package->getId(),
                'title'       => $package->getTitle(),
                'status'      => $package->getStatus(),
                'information' => $package->getInformation(),
            ];
        } else {
            $package = [
                'id'          => $package['id'],
                'title'       => $package['title'],
                'status'      => $package['status'],
                'information' => $package['information'],
            ];
        }

        $package['information'] = !empty($package['information']) ? json_decode($package['information'], true) : [];

        return $package;
    }
}