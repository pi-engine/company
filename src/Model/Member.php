<?php

namespace Company\Model;

class Member
{
    private mixed $id;
    private int   $company_id;
    private int   $user_id;
    private int   $time_create;
    private int   $time_update;
    private int   $status;
    private int   $is_default;
    private mixed $user_identity;
    private mixed $user_name;
    private mixed $user_email;
    private mixed $user_mobile;
    private mixed $first_name;
    private mixed $last_name;

    public function __construct(
        $company_id,
        $user_id,
        $time_create,
        $time_update,
        $status,
        $is_default,
        $user_identity = null,
        $user_name = null,
        $user_email = null,
        $user_mobile = null,
        $first_name = null,
        $last_name = null,
        $id = null
    ) {
        $this->company_id    = $company_id;
        $this->user_id       = $user_id;
        $this->time_create   = $time_create;
        $this->time_update   = $time_update;
        $this->status        = $status;
        $this->is_default    = $is_default;
        $this->user_identity = $user_identity;
        $this->user_name     = $user_name;
        $this->user_email    = $user_email;
        $this->user_mobile   = $user_mobile;
        $this->first_name    = $first_name;
        $this->last_name     = $last_name;
        $this->id            = $id;
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
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
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getTimeCreate(): int
    {
        return $this->time_create;
    }

    /**
     * @return int
     */
    public function getTimeUpdate(): int
    {
        return $this->time_update;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getIsDefault(): int
    {
        return $this->is_default;
    }

    public function getUserIdentity(): ?string
    {
        return $this->user_identity;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function getUserEmail(): ?string
    {
        return $this->user_email;
    }

    public function getUserMobile(): ?string
    {
        return $this->user_mobile;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }
}