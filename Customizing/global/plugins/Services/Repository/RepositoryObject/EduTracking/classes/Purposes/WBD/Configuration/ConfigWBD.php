<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD\Configuration;

/**
 * Immutable object to get configuration values for WBD purpose
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ConfigWBD
{
    const M_FIX_CONTACT = "fixed_contact";
    const M_COURSE_TUTOR = "course_tutor";
    const M_COURSE_ADMIN = "course_admin";
    const M_XCCL_CONTACT = "xccl_contact";

    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $available;

    /**
     * @var string
     */
    protected $contact;

    /**
     * @var int | null
     */
    protected $user_id;

    public function __construct(int $id, bool $available, string  $contact, ?int $user_id = null)
    {
        $this->id = $id;
        $this->available = $available;
        $this->contact = $contact;
        $this->user_id = $user_id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return int | null
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}
