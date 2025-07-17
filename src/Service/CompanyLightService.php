<?php

namespace Pi\Company\Service;

use Pi\Company\Repository\CompanyRepositoryInterface;

class CompanyLightService implements ServiceInterface
{
    /** @var CompanyRepositoryInterface */
    protected CompanyRepositoryInterface $companyRepository;

    /* @var array */
    protected array $config;

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
                                   $config
    ) {
        $this->companyRepository = $companyRepository;
        $this->config            = $config;
    }

    public function getCompanyDetails(int $userId): array
    {
        $rowSet = $this->companyRepository->getMemberListByCompany(['user_id' => $userId]);
        foreach ($rowSet as $row) {
            $company = $this->canonizeMemberCompany($row);
            if ((int)$company['is_default'] === 1) {
                return $company;
            }
        }

        return [];
    }

    public function canonizeMemberCompany($member): array
    {
        if (empty($member)) {
            return [];
        }

        if (is_object($member)) {
            $member = [
                'company_title' => $member->getTitle(),
                'company_id'    => $member->getCompanyId(),
                'is_default'    => $member->getIsDefault(),
            ];
        } else {
            $member = [
                'company_title' => $member['title'],
                'company_id'    => $member['company_id'],
                'is_default'    => $member['is_default'],
            ];
        }

        return $member;
    }
}