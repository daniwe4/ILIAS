<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\CourseMember\SignatureList\ConfigurableList;

class ConfigurableListConfig
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var array
     */
    protected $standard_fields;
    /**
     * @var array
     */
    protected $lp_fields;
    /**
     * @var array
     */
    protected $udf_fields;
    /**
     * @var array
     */
    protected $roles;
    /**
     * @var array
     */
    protected $additional;
    /**
     * @var bool
     */
    protected $default;
    /**
     * @var string
     */
    protected $mail_template_id;

    public function __construct(
        int $id,
        string $name,
        string $description,
        array $standard_fields,
        array $lp_fields,
        array $udf_fields,
        array $roles,
        array $additional,
        bool $default,
        string $mail_template_id
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->standard_fields = $standard_fields;
        $this->lp_fields = $lp_fields;
        $this->udf_fields = $udf_fields;
        $this->roles = $roles;
        $this->additional = $additional;
        $this->default = $default;
        $this->mail_template_id = $mail_template_id;
    }

    public function getId() : int
    {
        return $this->id;
    }


    public function getName() : string
    {
        return $this->name;
    }

    public function withName(string $name) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->name = $name;
        return $other;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function withDescription(string $description) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->description = $description;
        return $other;
    }

    public function getStandardFields() : array
    {
        return $this->standard_fields;
    }

    public function withStandardFields(array $standard_fields) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->standard_fields = $standard_fields;
        return $other;
    }

    public function getLpFields() : array
    {
        return $this->lp_fields;
    }

    public function withLpFields(array $lp_fields) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->lp_fields = $lp_fields;
        return $other;
    }

    public function getUdfFields() : array
    {
        return $this->udf_fields;
    }

    public function withUdfFields(array $udf_fields) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->udf_fields = $udf_fields;
        return $other;
    }

    public function getRoleFields() : array
    {
        return $this->roles;
    }

    public function withRoles(array $roles) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->roles = $roles;
        return $other;
    }

    public function getAdditionalFields() : array
    {
        return $this->additional;
    }

    public function withAdditional(array $additional) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->additional = $additional;
        return $other;
    }

    /**
     * @return bool
     */
    public function isDefault() : bool
    {
        return $this->default;
    }

    public function withDefault(bool $default) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->default = $default;
        return $other;
    }

    public function getMailTemplateId() : string
    {
        return $this->mail_template_id;
    }

    public function withMailTemplateId(string $mail_template_id) : ConfigurableListConfig
    {
        $other = clone $this;
        $other->mail_template_id = $mail_template_id;
        return $other;
    }
}
