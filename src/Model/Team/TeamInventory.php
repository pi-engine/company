<?php

namespace Pi\Company\Model\Team;

class TeamInventory
{
    private mixed  $id;
    private string $title;
    private int   $company_id;
    private int    $status;
    private mixed  $information;

    public function __construct(
        $title,
        $company_id,
        $status,
        $information,
        $id = null
    ) {
        $this->title       = $title;
        $this->company_id    = $company_id;
        $this->status      = $status;
        $this->information = $information;
        $this->id          = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getInformation(): ?string
    {
        return $this->information;
    }
}