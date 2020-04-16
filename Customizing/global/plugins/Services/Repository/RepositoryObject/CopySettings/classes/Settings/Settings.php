<?php

declare(strict_types=1);

namespace CaT\Plugins\CopySettings\Settings;

class Settings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $edit_title;

    /**
     * @var bool
     */
    protected $edit_target_groups;

    /**
     * @var bool
     */
    protected $edit_target_group_description;

    /**
     * @var bool
     */
    protected $edit_content;

    /**
     * @var bool
     */
    protected $edit_benefits;

    /**
     * @var bool
     */
    protected $edit_idd_learningtime;

    /**
     * @var int[]
     */
    protected $role_ids;

    /*
     * @var string
     */
    protected $time_mode;

    /**
     * @var int
     */
    protected $min_days_in_future;

    /**
     * @var bool
     */
    protected $edit_venue;

    /**
     * @var bool
     */
    protected $edit_provider;

    /**
     * @var bool
     */
    protected $additional_infos;

    /**
     * @var bool
     */
    protected $no_mail;

    /**
     * @var bool
     */
    protected $suppress_mail_delivery;

    /**
     * @var bool
     */
    protected $edit_gti;

    /**
     * @var bool
     */
    protected $edit_memberlimits;

    public function __construct(
        int $obj_id,
        bool $edit_title,
        bool $edit_target_groups,
        bool $edit_target_group_description,
        bool $edit_content,
        bool $edit_benefits,
        bool $edit_idd_learningtime,
        array $role_ids,
        bool $edit_venue,
        bool $edit_provider,
        bool $additional_infos,
        bool $no_mail,
        bool $edit_gti,
        bool $edit_memberlimits,
        bool $suppress_mail_delivery = true,
        string $time_mode = null,
        int $min_days_in_future = null
    ) {
        $this->obj_id = $obj_id;
        $this->edit_title = $edit_title;
        $this->edit_target_groups = $edit_target_groups;
        $this->edit_target_group_description = $edit_target_group_description;
        $this->edit_content = $edit_content;
        $this->edit_benefits = $edit_benefits;
        $this->edit_idd_learningtime = $edit_idd_learningtime;
        $this->role_ids = $role_ids;
        $this->time_mode = $time_mode;
        $this->edit_venue = $edit_venue;
        $this->edit_provider = $edit_provider;
        $this->additional_infos = $additional_infos;
        $this->no_mail = $no_mail;
        $this->edit_gti = $edit_gti;
        $this->edit_memberlimits = $edit_memberlimits;
        $this->suppress_mail_delivery = $suppress_mail_delivery;
        $this->min_days_in_future = $min_days_in_future;
    }

    /**
     * Get the obj id
     *
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * Get the option to edit the title
     *
     * @return bool
     */
    public function getEditTitle() : bool
    {
        return $this->edit_title;
    }

    /**
     * Get the option to edit the target groups
     *
     * @return bool
     */
    public function getEditTargetGroups() : bool
    {
        return $this->edit_target_groups;
    }

    /**
     * Get the option to edit the target group's description
     *
     * @return bool
     */
    public function getEditTargetGroupDescription() : bool
    {
        return (bool) $this->edit_target_group_description;
    }

    /**
     * Get the option to edit the content
     *
     * @return bool
     */
    public function getEditContent() : bool
    {
        return $this->edit_content;
    }

    /**
     * Get the option to edit the benefits
     *
     * @return bool
     */
    public function getEditBenefits() : bool
    {
        return $this->edit_benefits;
    }

    /**
     * Get the option to edit the idd learning time
     *
     * @return bool
     */
    public function getEditIDDLearningTime() : bool
    {
        return $this->edit_idd_learningtime;
    }

    /**

     * Get id if role creator will assigned
     *
     * @return int[]
     */
    public function getRoleIds() : array
    {
        return $this->role_ids;
    }

    /**
     * @return string | null
     */
    public function getTimeMode()
    {
        return $this->time_mode;
    }

    /**
     * @return int | null
     */
    public function getMinDaysInFuture()
    {
        return $this->min_days_in_future;
    }

    public function getEditVenue() : bool
    {
        return $this->edit_venue;
    }

    public function getEditProvider() : bool
    {
        return $this->edit_provider;
    }

    public function getAdditionalInfos() : bool
    {
        return $this->additional_infos;
    }

    public function getNoMail() : bool
    {
        return $this->no_mail;
    }

    public function getSuppressMailDelivery() : bool
    {
        return $this->suppress_mail_delivery;
    }

    public function isEditGti() : bool
    {
        return $this->edit_gti;
    }

    public function withEditTitle(bool $edit_title) : Settings
    {
        $clone = clone $this;
        $clone->edit_title = $edit_title;
        return $clone;
    }

    public function withEditTargetGroups(bool $edit_target_groups) : Settings
    {
        $clone = clone $this;
        $clone->edit_target_groups = $edit_target_groups;
        return $clone;
    }

    public function withEditTargetGroupDescription(bool $edit_target_group_description) : Settings
    {
        $clone = clone $this;
        $clone->edit_target_group_description = $edit_target_group_description;
        return $clone;
    }

    public function withEditContent(bool $edit_content) : Settings
    {
        $clone = clone $this;
        $clone->edit_content = $edit_content;
        return $clone;
    }

    public function withEditBenefits(bool $edit_benefits) : Settings
    {
        $clone = clone $this;
        $clone->edit_benefits = $edit_benefits;
        return $clone;
    }

    public function withEditIDDLearningTime(bool $edit_idd_learningtime) : Settings
    {
        $clone = clone $this;
        $clone->edit_idd_learningtime = $edit_idd_learningtime;
        return $clone;
    }

    public function withRoleIds(array $role_ids) : Settings
    {
        $clone = clone $this;
        $clone->role_ids = $role_ids;
        return $clone;
    }

    public function withTimeMode(string $time_mode = null) : Settings
    {
        $clone = clone $this;
        $clone->time_mode = $time_mode;
        return $clone;
    }

    public function withMinDaysInFuture(int $min_days_in_future = null) : Settings
    {
        $clone = clone $this;
        $clone->min_days_in_future = $min_days_in_future;
        return $clone;
    }

    public function withEditVenue(bool $edit_venue) : Settings
    {
        $clone = clone $this;
        $clone->edit_venue = $edit_venue;
        return $clone;
    }

    public function withEditProvider(bool $edit_provider) : Settings
    {
        $clone = clone $this;
        $clone->edit_provider = $edit_provider;
        return $clone;
    }

    public function withAdditionalInfos(bool $additional_infos) : Settings
    {
        $clone = clone $this;
        $clone->additional_infos = $additional_infos;
        return $clone;
    }

    public function withNoMail(bool $no_mail) : Settings
    {
        $clone = clone $this;
        $clone->no_mail = $no_mail;
        return $clone;
    }

    public function withSuppressMailDelivery(bool $suppress_mail_delivery) : Settings
    {
        $clone = clone $this;
        $clone->suppress_mail_delivery = $suppress_mail_delivery;
        return $clone;
    }

    public function withEditGti(bool $edit_gti) : Settings
    {
        $clone = clone $this;
        $clone->edit_gti = $edit_gti;
        return $clone;
    }

    public function withEditMemberlimits(bool $edit_memberlimits) : Settings
    {
        $clone = clone $this;
        $clone->edit_memberlimits = $edit_memberlimits;
        return $clone;
    }

    public function getEditMemberlimits() : bool
    {
        return $this->edit_memberlimits;
    }
}
