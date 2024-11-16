<?php

namespace Pi\Company\Model;

class Inventory
{
    private mixed  $id;
    private string $title;
    private string $text_description;
    private mixed  $setting;
    private int    $user_id;
    private int    $package_id;
    private int    $reseller_id;
    private int    $industry_id;
    private int    $time_create;
    private int    $time_update;
    private int    $status;
    private mixed  $address_1;
    private mixed  $address_2;
    private mixed  $country;
    private mixed  $state;
    private mixed  $city;
    private mixed  $zip_code;
    private mixed  $phone;
    private mixed  $website;
    private mixed  $email;
    private mixed $user_identity;
    private mixed $user_name;
    private mixed $user_email;
    private mixed $user_mobile;
    private mixed $package_title;

    public function __construct(
        $title,
        $text_description,
        $setting,
        $user_id,
        $package_id,
        $reseller_id,
        $industry_id,
        $time_create,
        $time_update,
        $status,
        $address_1,
        $address_2,
        $country,
        $state,
        $city,
        $zip_code,
        $phone,
        $website,
        $email,
        $user_identity = null,
        $user_name = null,
        $user_email = null,
        $user_mobile = null,
        $package_title = null,
        $id = null
    ) {
        $this->title            = $title;
        $this->text_description = $text_description;
        $this->setting          = $setting;
        $this->user_id          = $user_id;
        $this->package_id       = $package_id;
        $this->reseller_id      = $reseller_id;
        $this->industry_id      = $industry_id;
        $this->time_create      = $time_create;
        $this->time_update      = $time_update;
        $this->status           = $status;
        $this->address_1        = $address_1;
        $this->address_2        = $address_2;
        $this->country          = $country;
        $this->state            = $state;
        $this->city             = $city;
        $this->zip_code         = $zip_code;
        $this->phone            = $phone;
        $this->website          = $website;
        $this->email            = $email;
        $this->user_identity = $user_identity;
        $this->user_name     = $user_name;
        $this->user_email    = $user_email;
        $this->user_mobile   = $user_mobile;
        $this->package_title    = $package_title;
        $this->id               = $id;
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
     * @return string
     */
    public function getTextDescription(): string
    {
        return $this->text_description;
    }

    /**
     * @return string|null
     */
    public function getSetting(): ?string
    {
        return $this->setting;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getPackageId(): int
    {
        return $this->package_id;
    }

    public function getResellerId(): int
    {
        return $this->reseller_id;
    }

    public function getIndustryId(): int
    {
        return $this->industry_id;
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

    /**
     * @return string|null
     */
    public function getAddress1(): ?string
    {
        return $this->address_1;
    }

    /**
     * @return string|null
     */
    public function getAddress2(): ?string
    {
        return $this->address_2;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
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

    public function getPackageTitle(): ?string
    {
        return $this->package_title;
    }
}