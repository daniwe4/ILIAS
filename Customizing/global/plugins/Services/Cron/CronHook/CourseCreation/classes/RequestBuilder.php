<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace CaT\Plugins\CourseCreation;

use ILIAS\TMS\CourseCreation;

/**
 * Creates request by using the database.
 */
class RequestBuilder implements CourseCreation\RequestBuilder
{
    /**
     * @var RequestDB
     */
    protected $request_db;

    /**
     * @var int|null
     */
    protected $current_user_id;

    /**
     * @var string|null
     */
    protected $session_id;

    /**
     * @var int|null
     */
    protected $crs_ref_id;

    /**
     * @var int|null
     */
    protected $new_parent_ref_id;

    /**
     * @var array<int,int>
     */
    protected $copy_options;

    /**
     * @var array<int, mixed>
     */
    protected $configuration;

    public function __construct(RequestDB $request_db)
    {
        $this->request_db = $request_db;
        $this->user_id = null;
        $this->session_id = null;
        $this->crs_ref_id = null;
        $this->new_parent_ref_id = null;
        $this->copy_options = [];
        $this->configuration = [];
    }

    /**
     * @inheritdoc
     */
    public function setUserIdAndSessionId($user_id, $session_id)
    {
        assert('is_int($user_id)');
        assert('is_string($session_id)');

        $this->user_id = $user_id;
        $this->session_id = $session_id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCourseRefId($crs_ref_id)
    {
        assert('is_int($crs_ref_id)');

        $this->crs_ref_id = $crs_ref_id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setNewParentRefId($new_parent_ref_id)
    {
        assert('is_int($new_parent_ref_id)');

        $this->new_parent_ref_id = $new_parent_ref_id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRequest(\DateTime $requested_ts)
    {
        return $this->request_db->create(
            $this->user_id,
            $this->session_id,
            $this->crs_ref_id,
            $this->new_parent_ref_id,
            $this->copy_options,
            $this->configuration,
            $requested_ts
        );
    }

    /**
     * @inheritdoc
     */
    public function setCopyOptionFor($ref_id, $copy_option)
    {
        $this->copy_options[$ref_id] = $copy_option;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addConfigurationFor(\ilObject $object, $data)
    {
        assert('method_exists($object, "afterCourseCreation")');
        if (!isset($this->configuration[$object->getRefId()])) {
            $this->configuration[$object->getRefId()] = [];
        }
        $this->configuration[$object->getRefId()][] = $data;
        return $this;
    }
}
