<?php

namespace Pi\Company\Model;

class Package
{
    private mixed  $id;
    private string $title;
    private int    $status;
    private mixed  $information;

    public function __construct(
        $title,
        $status,
        $information,
        $id = null
    ) {
        $this->title       = $title;
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