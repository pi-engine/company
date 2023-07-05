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

    public function __construct(
        $company_id,
        $user_id,
        $time_create,
        $time_update,
        $status,
        $id = null
    ) {
        $this->company_id  = $company_id;
        $this->user_id     = $user_id;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
        $this->status      = $status;
        $this->id          = $id;
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
}