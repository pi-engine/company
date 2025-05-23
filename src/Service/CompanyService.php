<?php

namespace Pi\Company\Service;

use Fig\Http\Message\StatusCodeInterface;
use Pi\Company\Repository\CompanyRepositoryInterface;
use Pi\Core\Service\CacheService;
use Pi\Core\Service\UtilityService;
use Pi\Notification\Service\NotificationService;
use Pi\User\Service\AccountService;
use Pi\User\Service\RoleService;
use Random\RandomException;

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

    protected string $companyKeyPattern = 'company_%s';

    public string $companyAdminRole         = 'companyadmin';
    public string $companySuperUserRole     = 'companysuperuser';
    public string $companyGovernanceManager = 'companygovernancemanager';
    public string $companyGovernanceOfficer = 'companygovernanceofficer';
    public string $companyGovernanceViewer  = 'companygovernanceviewer';
    public string $companyAssessmentManager = 'companyassessmentmanager';
    public string $companyAssessmentOfficer = 'companyassessmentofficer';
    public string $companyAssessmentViewer  = 'companyassessmentviewer';
    public string $companyComplianceManager = 'companycompliancemanager';
    public string $companyComplianceOfficer = 'companycomplianceofficer';
    public string $companyComplianceViewer  = 'companycomplianceviewer';
    public string $companyRiskManager       = 'companyriskmanager';
    public string $companyRiskOfficer       = 'companyriskofficer';
    public string $companyRiskViewer        = 'companyriskviewer';
    public string $companyAuditManager      = 'companyauditmanager';
    public string $companyAuditor           = 'companyauditor';
    public string $companyAuditViewer       = 'companyauditviewer';
    public string $companyMemberRole        = 'companymember';
    public string $companyExternalRole      = 'companyexternal';
    public string $companyViewerRole        = 'companyviewer';
    public int    $industryId               = 1;
    public int    $packageId                = 1;
    public string $packageExpire            = '+4 weeks';
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

    protected array $profileAdminFields
        = [
            'title',
            'text_description',
            'package_id',
            'reseller_id',
            'industry_id',
            'status',
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

    public int $companyTtl = 31536000;

    public int $packageTtl = 31536000;

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        AccountService             $accountService,
        RoleService                $roleService,
        CacheService               $cacheService,
        NotificationService        $notificationService,
        UtilityService             $utilityService,
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
        // Get cached user
        $cacheUser = $this->accountService->getUserFromCache($account['id']);

        // Set result
        $result = [
            'result' => true,
            'data'   => [
                'user_id'    => $account['id'],
                'company_id' => 0,
                'package_id' => 0,
                'project_id' => 0,
                'user'       => $account,
                'roles'      => $cacheUser['roles'],
                'member'     => null,
                'company'    => null,
                'is_admin'   => 0,
                'package'    => null,
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
        $memberParams = ['is_default' => 1];
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
            || in_array($this->companySuperUserRole, $result['data']['roles'])
        ) {
            $result['data']['is_admin'] = 1;
        }

        // Set and clean user authorization cache
        $account = [
            'id'               => $result['data']['user_id'],
            'is_company_setup' => true,
            'company_id'       => $result['data']['company']['id'],
            'company_title'    => $result['data']['company']['title'],
            'roles'            => $result['data']['roles'],
            'authorization'    => [
                'user_id'    => $result['data']['user_id'],
                'company_id' => $result['data']['company_id'],
                'package_id' => $result['data']['package_id'],
                'project_id' => $result['data']['project_id'],
                'is_admin'   => $result['data']['is_admin'],
                'company'    => $result['data']['company'],
            ],
        ];

        // Clean company data
        unset($account['authorization']['company']['setting']);

        // Save to cache
        $this->accountService->manageUserCache($account);

        // Set company cache
        $this->cacheService->setItem(sprintf($this->companyKeyPattern, $result['data']['company_id']), $result['data']['company'], $this->companyTtl);

        return $result;
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

    public function refreshToken($authorization, $tokenId): array
    {
        // Set account
        $account = array_merge(
            $authorization['user'],
            [
                'company_id'    => $authorization['company_id'],
                'company_title' => $authorization['company']['title'],
                'roles'         => $authorization['roles'],
            ]
        );

        return $this->accountService->refreshToken($account, $tokenId);
    }

    /**
     * @throws RandomException
     */
    public function registerCompany($account): array
    {
        // Set company params
        $addParams = [
            'title'            => $account['company'] ?? sprintf('%s company', bin2hex(random_bytes(4))),
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
                'analytic' => [],
                'general'  => [],
                'context'  => [],
                'wizard'   => [
                    'is_completed' => false,
                    'time_start'   => time(),
                    'time_end'     => 0,
                    'steps'        => $this->wizardSteps,
                ],
                'package'  => [
                    'time_start'  => time(),
                    'time_renew'  => time(),
                    'time_expire' => strtotime($this->packageExpire),
                    'renew_count' => 1,
                    'user_count'  => 100,
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

    public function registerCompanyByAdmin($params, $operator): array
    {
        // Get user account
        $account = $this->accountService->getAccount(['user_id' => $params['user_id']]);

        // Set company params
        $addParams = [
            'title'            => $params['title'],
            'user_id'          => $account['id'],
            'time_create'      => time(),
            'time_update'      => time(),
            'phone'            => $params['mobile'] ?? $account['mobile'] ?? null,
            'email'            => $params['email'] ?? $account['email'] ?? null,
            'status'           => 1,
            'industry_id'      => $params['industry_id'],
            'package_id'       => $params['package_id'],
            'text_description' => '',
            'setting'          => json_encode([
                'analytic' => [],
                'general'  => [],
                'context'  => [],
                'wizard'   => [
                    'is_completed' => true,
                    'time_start'   => time(),
                    'time_end'     => time(),
                    'steps'        => [
                        'user_profile'    => true,
                        'company_profile' => true,
                        'voucher'         => true,
                    ],
                ],
                'package'  => [
                    'time_start'  => time(),
                    'time_renew'  => time(),
                    'time_expire' => strtotime($this->packageExpire),
                    'renew_count' => 1,
                    'user_count'  => 100,
                ],
            ]),
        ];

        // Add company
        $company = $this->addCompany($addParams);

        // Add member
        $this->addMember($account, $company, $params, $operator);

        return $company;
    }

    public function addCompany($params): array
    {
        $company = $this->companyRepository->addCompany($params);
        $company = $this->canonizeCompany($company);

        // Set company cache
        $this->cacheService->setItem(sprintf($this->companyKeyPattern, $company['id']), $company, $this->companyTtl);

        return $company;
    }

    public function getCompany(int $companyId): array
    {
        $where   = ['id' => $companyId];
        $company = $this->companyRepository->getCompany($where);
        return $this->canonizeCompany($company);
    }

    public function getCompanyList($params): array
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
        if (isset($params['title']) && !empty($params['title'])) {
            $listParams['title'] = $params['title'];
        }
        if (isset($params['reseller_id']) && !empty($params['reseller_id'])) {
            $listParams['reseller_id'] = $params['reseller_id'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $listParams['status'] = $params['status'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $listParams['id'] = $params['id'];
        } elseif (isset($params['id']) && empty($params['id'])) {
            return [
                'result' => true,
                'data'   => [
                    'list'      => [],
                    'paginator' => [
                        'count' => 0,
                        'limit' => $limit,
                        'page'  => $page,
                    ],
                ],
                'error'  => [],
            ];
        }

        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getCompanyList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeCompany($row);
        }

        // Get count
        $count = $this->companyRepository->getCompanyCount($listParams);

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

    public function getCompanyListLight($params): array
    {
        $listParams = [];
        if (isset($params['reseller_id']) && !empty($params['reseller_id'])) {
            $listParams['reseller_id'] = $params['reseller_id'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $listParams['status'] = $params['status'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $listParams['id'] = $params['id'];
        } elseif (isset($params['id']) && empty($params['id'])) {
            return [];
        }

        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getCompanyList($listParams);
        foreach ($rowSet as $row) {
            $list[$row->getId()] = $this->canonizeCompanyLight($row);
        }

        return $list;
    }

    public function getCompanyCount($params): int
    {
        $listParams = [];
        if (isset($params['title']) && !empty($params['title'])) {
            $listParams['title'] = $params['title'];
        }
        if (isset($params['reseller_id']) && !empty($params['reseller_id'])) {
            $listParams['reseller_id'] = $params['reseller_id'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $listParams['status'] = $params['status'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $listParams['id'] = $params['id'];
        }

        // Get count
        return $this->companyRepository->getCompanyCount($listParams);
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
        $this->cacheService->deleteItem(sprintf($this->companyKeyPattern, (int)$company['id']));
        $this->cacheService->setItem(sprintf($this->companyKeyPattern, (int)$company['id']), $company, $this->companyTtl);

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
        $setting['analytic'] = $setting['analytic'] ?? [];
        $setting['general']  = $setting['general'] ?? [];
        $setting['context']  = $setting['context'] ?? [];
        $setting['wizard']   = $setting['wizard'] ?? [];
        $setting['package']  = $setting['package'] ?? [
            'time_start'  => time(),
            'time_renew'  => time(),
            'time_expire' => strtotime($this->packageExpire),
            'renew_count' => 1,
            'user_count'  => 100,
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

            case 'analytic':
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
        $profileParams = [
            'time_update' => time(),
            'setting'     => json_encode($setting, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];

        // Update company
        $this->companyRepository->updateCompany((int)$authorization['company_id'], $profileParams);

        // Set company cache
        $company = $this->getCompany((int)$authorization['company_id']);
        $this->cacheService->setItem(sprintf($this->companyKeyPattern, (int)$company['id']), $company, $this->companyTtl);

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

    public function updateCompanyByAdmin($company, $params, $type = null): array
    {
        // Set update update
        $companyParams = [
            'time_update' => time(),
        ];

        // Update params
        foreach ($params as $key => $value) {
            if (in_array($key, $this->profileAdminFields)) {
                if (is_numeric($value)) {
                    $companyParams[$key] = (int)$value;
                } elseif (is_string($value)) {
                    $companyParams[$key] = $value;
                } elseif (empty($value)) {
                    $companyParams[$key] = null;
                }
            }
        }

        if (!is_null($type)) {
            // Set context
            $setting = $company['setting'] ?? [];

            // Set default params
            $setting['analytic'] = $setting['analytic'] ?? [];
            $setting['general']  = $setting['general'] ?? [];
            $setting['context']  = $setting['context'] ?? [];
            $setting['wizard']   = $setting['wizard'] ?? [];
            $setting['package']  = $setting['package'] ?? [
                'time_start'  => time(),
                'time_renew'  => time(),
                'time_expire' => strtotime($this->packageExpire),
                'renew_count' => 1,
                'user_count'  => 100,
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
                case 'package':
                    $setting['package']['renew_count'] = $setting['package']['renew_count'] + 1;
                    $setting['package']['time_expire'] = $params['package_expire']
                        ? strtotime(sprintf('%s 18:00:00', $params['package_expire']))
                        : strtotime($this->packageExpire);
                    break;
                case 'analytic':
                case 'general':
                case 'context':
                default:
                    // Update data
                    foreach ($params as $key => $value) {
                        $setting[$type][$key] = $value;
                    }
                    break;
            }

            // Set update
            $companyParams['setting'] = json_encode($setting, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        }

        // Update company
        $this->companyRepository->updateCompany((int)$company['id'], $companyParams);

        // Set company cache
        $company = $this->getCompany((int)$company['id']);
        $this->cacheService->deleteItem(sprintf($this->companyKeyPattern, (int)$company['id']));
        $this->cacheService->setItem(sprintf($this->companyKeyPattern, (int)$company['id']), $company, $this->companyTtl);

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

    public function getCompanyFromCache(int $companyId): array
    {
        return $this->cacheService->getItem(sprintf($this->companyKeyPattern, $companyId));
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

        // Set section
        $section = $params['section'] ?? 'api';

        // Get roles
        $roleList = $this->roleService->getRoleAccountList(array_keys($list), $section);
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

    public function getMemberCount($params): int
    {
        return $this->companyRepository->getMemberCount($params);
    }

    public function addMemberByCompany($company, $params, $operator): array
    {
        // Add or Get account
        $account = $this->accountService->addOrGetAccount($params);

        // Add or Get member
        return $this->addMember($account, $company, $params, $operator);
    }

    public function addMemberByAdmin($params, $operator): array
    {
        // Add or Get account
        $account = $this->accountService->addOrGetAccount($params);

        // Get company
        $company = $this->getCompany((int)$params['company_id']);

        // Add or Get member
        return $this->addMember($account, $company, $params, $operator);
    }

    public function addMember($account, $company, $params, $operator = []): array
    {
        // Check member not exist
        $member = $this->getMember($account['id'], ['company_id' => $company['id']]);
        if (!empty($member)) {
            return $member;
        }

        // Set member params
        $memberParams = [
            'company_id'  => $company['id'],
            'user_id'     => $account['id'],
            'time_create' => time(),
            'time_update' => time(),
            'status'      => 1,
        ];

        // Add member
        $member = $this->companyRepository->addMember($memberParams);
        $member = $this->canonizeMember($member);

        // Add roles
        $account['roles'] = $params['roles'] ?? [$this->companyMemberRole];
        foreach ($account['roles'] as $role) {
            $this->roleService->addRoleAccount($account, $role, 'api', $operator);
        }

        // Check admin for a cache
        $isAdmin = 0;
        if (in_array($this->companyAdminRole, $account['roles'])
            || in_array($this->companySuperUserRole, $account['roles'])
        ) {
            $isAdmin = 1;
        }

        // Set company setup
        $account['is_company_setup'] = true;
        $account['company_id']       = $company['id'];
        $account['company_title']    = $company['title'];

        // Set and clean user authorization cache
        $account['authorization'] = [
            'user_id'    => $company['user_id'],
            'company_id' => $company['company_id'],
            'package_id' => $company['package_id'],
            'project_id' => $company['project_id'],
            'is_admin'   => $isAdmin,
            'company'    => $company,
        ];

        // Clean company data
        unset($account['authorization']['company']['setting']);

        // Save to cache
        $this->accountService->manageUserCache($account);

        return $member;
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

    public function updateMemberByAdmin($params, $operator): array
    {
        // Get member
        $member = $this->getMember($params['user_id'], ['company_id' => $params['company_id']]);

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
            $this->accountService->updateAccountRoles($params['roles'], $account, 'api', $operator);
        }

        return $account;
    }

    public function getPackage(int $packageId): array
    {
        $where   = ['id' => $packageId];
        $package = $this->companyRepository->getPackage($where);
        return $this->canonizePackage($package);
    }

    public function getPackageList($params): array
    {
        // Get a list
        $list   = [];
        $rowSet = $this->companyRepository->getPackageList($params);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizePackage($row);
        }

        return $list;
    }

    public function addPackage($params): array
    {
        // Set package params
        $addParams = [
            'title'       => $params['title'],
            'status'      => 1,
            'information' => json_encode(
                [
                    'type'   => $params['type'] ?? 'full',
                    'expire' => $params['expire'] ?? $this->packageExpire,
                    'access' => array_values($params['access']),
                ],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            ),
        ];

        // Add package
        $package = $this->companyRepository->addPackage($addParams);
        $package = $this->canonizePackage($package);

        // Set to cache
        $this->cacheService->setItem(sprintf('package-%s', $package['id']), $package, $this->packageTtl);

        return $package;
    }

    public function getTeam(int $teamId): array
    {
        $where = ['id' => $teamId];
        $team  = $this->companyRepository->getTeam($where);
        return $this->canonizeTeam($team);
    }

    public function updatePackage($package, $params): array
    {
        $packageParams = [
            'title'       => $params['title'] ?? $package['title'],
            'status'      => $params['status'] ?? $package['status'],
            'information' => json_encode(
                [
                    'type'   => $params['type'] ?? $package['information']['status'],
                    'expire' => $params['expire'] ?? $package['information']['expire'],
                    'access' => array_values($params['access'] ?? $package['information']['access']),
                ],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            ),
        ];

        // Update a company
        $this->companyRepository->updatePackage((int)$package['id'], $packageParams);
        $package = $this->getPackage($package['id']);

        // Set to cache
        $this->cacheService->setItem(sprintf('package-%s', $package['id']), $package, $this->packageTtl);

        return $package;
    }

    public function addTeam($authorization, $params): array
    {
        // Set members
        $members = [];
        if (isset($params['members']) && !empty($params['members'])) {
            $members = $params['members'];
            unset($params['members']);
        }

        // Set team params
        $addParams = [
            'title'       => $params['title'],
            'company_id'  => $authorization['company_id'],
            'status'      => 1,
            'information' => json_encode(
                [
                    'description' => $params['description'] ?? '',
                ],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            ),
        ];

        // Add team
        $team = $this->companyRepository->addTeam($addParams);
        $team = $this->canonizeTeam($team);

        // Save members
        if (!empty($members)) {
            foreach ($members as $member) {
                if (isset($member['user_id']) && is_numeric($member['user_id'])) {
                    // Set team member params
                    $addMemberParams = [
                        'team_id'   => $team['id'],
                        'user_id'   => $member['user_id'],
                        'team_role' => $member['team_role'] ?? '',
                    ];

                    // Add member
                    $team['members'][] = $this->addTeamMember($authorization, $addMemberParams);
                }
            }
        }

        return $team;
    }

    public function updateTeam($team, $params): array
    {
        $updateParams = [
            'title'       => $params['title'] ?? $team['title'],
            'information' => json_encode(
                $params,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            ),
        ];

        // Update a company
        $this->companyRepository->updateTeam((int)$team['id'], $updateParams);
        return $this->getTeam($team['id']);
    }

    public function listTeam($authorization, $params): array
    {
        // Set list params
        $listParams = ['company_id' => $authorization['company_id']];

        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getTeamList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeTeam($row);
        }

        return $list;
    }

    public function listTeamByAdmin($params): array
    {
        // Set list params
        $listParams = [];
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $listParams['company_id'] = $params['company_id'];
        }

        // Get list
        $list   = [];
        $rowSet = $this->companyRepository->getTeamList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeTeam($row);
        }

        return $list;
    }

    public function addTeamMember($authorization, $params): array
    {
        // Set member params
        $addParams = [
            'company_id'  => $authorization['company_id'],
            'team_id'     => $params['team_id'],
            'user_id'     => $params['user_id'],
            'time_create' => time(),
            'time_update' => time(),
            'status'      => 1,
            'team_role'   => $params['team_role'] ?? '',
        ];

        // Add member
        $member = $this->companyRepository->addTeamMember($addParams);
        return $this->canonizeTeamMember($member);
    }

    public function getTeamMember(int $memberId): array
    {
        $where  = ['id' => $memberId];
        $member = $this->companyRepository->getTeamMember($where);
        return $this->canonizeTeamMember($member);
    }

    public function updateTeamMember($member, $params): array
    {
        $updateParams = [
            'title'       => $params['team_role'] ?? $member['team_role'],
            'time_update' => time(),
        ];

        // Update a team member
        $this->companyRepository->updateTeamMember((int)$member['id'], $updateParams);
        return $this->getTeamMember($member['id']);
    }

    public function deleteTeamMember($member): void
    {
        $this->companyRepository->deleteTeamMember($member['id']);
    }

    public function listTeamMember($authorization, $params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['time_create DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order'      => $order,
            'offset'     => $offset,
            'limit'      => $limit,
            'company_id' => $authorization['company_id'],
        ];

        if (isset($params['team_id']) && !empty($params['team_id'])) {
            $listParams['team_id'] = $params['team_id'];
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
        $rowSet = $this->companyRepository->getTeamMemberList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeTeamMember($row);
        }

        // Get count
        $count = $this->companyRepository->getTeamMemberCount($listParams);

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

    public function listTeamMemberByAdmin($params): array
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
        if (isset($params['team_id']) && !empty($params['team_id'])) {
            $listParams['team_id'] = $params['team_id'];
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
        $rowSet = $this->companyRepository->getTeamMemberList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeTeamMember($row);
        }

        // Get count
        $count = $this->companyRepository->getTeamMemberCount($listParams);

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

    public function getRoleMemberList($userId): array
    {
        return $this->roleService->getRoleAccountList($userId, 'api');
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
                'user_identity'    => $company->getUserIdentity(),
                'user_name'        => $company->getUserName(),
                'user_email'       => $company->getUserEmail(),
                'user_mobile'      => $company->getUserMobile(),
                'package_title'    => $company->getPackageTitle(),
                'setting'          => $company->getSetting(),
            ];
        } else {
            $company = [
                'id'               => $company['id'],
                'title'            => $company['title'],
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
                'user_name'        => $company['user_name'],
                'user_email'       => $company['user_email'],
                'user_mobile'      => $company['user_mobile'],
                'package_title'    => $company['package_title'],
                'setting'          => $company['setting'],
            ];
        }

        $company['setting']          = !empty($company['setting']) ? json_decode($company['setting'], true) : [];
        $company['hash']             = hash('sha256', sprintf('%s-%s', $company['id'], $company['time_create']));
        $company['slug']             = sprintf('company-%s-%s', $company['id'], $this->config['platform']);
        $company['is_company_setup'] = true; // ToDo: remove it

        // Set default params
        $company['setting']['analytic'] = $company['setting']['analytic'] ?? [];
        $company['setting']['general']  = $company['setting']['general'] ?? [];
        $company['setting']['context']  = $company['setting']['context'] ?? [];
        $company['setting']['wizard']   = $company['setting']['wizard'] ?? [];
        $company['setting']['package']  = $company['setting']['package'] ?? [];

        if (isset($company['setting']['package']) && !empty($company['setting']['package'])) {
            $timeParams                                          = ['pattern' => 'dd/MM/yyyy', 'format' => 'd/m/Y'];
            $company['setting']['package']['time_expire_format'] = date('Y/m/d', $company['setting']['package']['time_expire']);
            $company['setting']['package']['time_start_view']    = $this->utilityService->date($company['setting']['package']['time_start'], $timeParams);
            $company['setting']['package']['time_renew_view']    = $this->utilityService->date($company['setting']['package']['time_renew'], $timeParams);
            $company['setting']['package']['time_expire_view']   = $this->utilityService->date($company['setting']['package']['time_expire'], $timeParams);
        }

        return $company;
    }

    public function canonizeCompanyLight($company): array
    {
        if (empty($company)) {
            return [];
        }

        if (is_object($company)) {
            $company = [
                'id'          => $company->getId(),
                'title'       => $company->getTitle(),
                'user_id'     => $company->getUserId(),
                'package_id'  => $company->getPackageId(),
                'reseller_id' => $company->getResellerId(),
                'industry_id' => $company->getIndustryId(),
                'status'      => $company->getStatus(),
            ];
        } else {
            $company = [
                'id'          => $company['id'],
                'title'       => $company['title'],
                'user_id'     => $company['user_id'],
                'package_id'  => $company['package_id'],
                'reseller_id' => $company['reseller_id'],
                'status'      => $company['status'],
            ];
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

    public function canonizeTeam($team): array
    {
        if (empty($team)) {
            return [];
        }

        if (is_object($team)) {
            $team = [
                'id'          => $team->getId(),
                'title'       => $team->getTitle(),
                'company_id'  => $team->getCompanyId(),
                'status'      => $team->getStatus(),
                'information' => $team->getInformation(),
            ];
        } else {
            $team = [
                'id'          => $team['id'],
                'title'       => $team['title'],
                'company_id'  => $team['company_id'],
                'status'      => $team['status'],
                'information' => $team['information'],
            ];
        }

        $team['information'] = !empty($team['information']) ? json_decode($team['information'], true) : [];

        return $team;
    }

    public function canonizeTeamMember($teamMember): array
    {
        if (empty($teamMember)) {
            return [];
        }

        if (is_object($teamMember)) {
            $teamMember = [
                'id'            => $teamMember->getId(),
                'company_id'    => $teamMember->getCompanyId(),
                'team_id'       => $teamMember->getTeamId(),
                'user_id'       => $teamMember->getUserId(),
                'time_create'   => $teamMember->getTimeCreate(),
                'time_update'   => $teamMember->getTimeUpdate(),
                'status'        => $teamMember->getStatus(),
                'team_role'     => $teamMember->getTeamRole(),
                'team_title'    => $teamMember->getTeamTitle(),
                'user_identity' => $teamMember->getUserIdentity(),
                'user_name'     => $teamMember->getUserName(),
                'user_email'    => $teamMember->getUserEmail(),
                'user_mobile'   => $teamMember->getUserMobile(),
                'first_name'    => $teamMember->getFirstName(),
                'last_name'     => $teamMember->getLastName(),
            ];
        } else {
            $teamMember = [
                'id'            => $teamMember['id'],
                'company_id'    => $teamMember['company_id'],
                'team_id'       => $teamMember['team_id'],
                'user_id'       => $teamMember['user_id'],
                'time_create'   => $teamMember['time_create'],
                'time_update'   => $teamMember['time_update'],
                'status'        => $teamMember['status'],
                'team_role'     => $teamMember['team_role'],
                'team_title'    => $teamMember['team_title'],
                'user_identity' => $teamMember['user_identity'],
                'user_name'     => $teamMember['user_name'],
                'user_email'    => $teamMember['user_email'],
                'user_mobile'   => $teamMember['user_mobile'],
                'first_name'    => $teamMember['first_name'],
                'last_name'     => $teamMember['last_name'],
            ];
        }

        // Set time view
        $teamMember['time_create_view'] = $this->utilityService->date($teamMember['time_create']);
        $teamMember['time_update_view'] = $this->utilityService->date($teamMember['time_update']);

        return $teamMember;
    }
}