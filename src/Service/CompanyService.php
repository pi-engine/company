<?php

namespace Company\Service;

use Company\Repository\CompanyRepositoryInterface;
use Notification\Service\NotificationService;
use User\Service\AccountService;
use User\Service\UtilityService;

class CompanyService implements ServiceInterface
{
    /** @var AccountService */
    protected AccountService $accountService;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /** @var NotificationService */
    protected NotificationService $notificationService;

    /** @var CompanyRepositoryInterface */
    protected CompanyRepositoryInterface $companyRepository;

    /* @var array */
    protected array $config;

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        AccountService $accountService,
        NotificationService $notificationService,
        UtilityService $utilityService,
        $config
    ) {
        $this->companyRepository   = $companyRepository;
        $this->accountService      = $accountService;
        $this->notificationService = $notificationService;
        $this->utilityService      = $utilityService;
        $this->config              = $config;
    }
}