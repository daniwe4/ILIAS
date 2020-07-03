<?php

namespace CaT\Plugins\OnlineSeminar\VC\CSN;

use CaT\Plugins\OnlineSeminar\VC\Participant as VCP;

/**
 * Information for each participant of an CSN VC
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Participant implements VCP
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * ILIAS user id
     *
     * @var int
     */
    protected $user_id;

    /**
     * ILIAS user name
     *
     * @var string
     */
    protected $user_name;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var int
     */
    protected $minutes;

    /**
     * @param int 	$id
     * @param int 	$obj_id
     * @param string 	$name
     * @param string 	$email
     * @param string 	$phone
     * @param string 	$company
     * @param int 	$minutes
     * @param int | null	$user_id
     * @param string 	$user_name
     */
    public function __construct(
        int $id,
        int $obj_id,
        string $name,
        string $email,
        string $phone,
        string $company,
        int $minutes,
        string $user_name,
        ?int $user_id = null
    ) {
        $this->id = $id;
        $this->obj_id = $obj_id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->company = $company;
        $this->minutes = $minutes;
        $this->user_id = $user_id;
        $this->user_name = $user_name;
    }

    /**
     * Get unique id of user
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the obj id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * get user id of participant
     *
     * @return string |  null
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Get user name of participant
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * get the name of participant
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the email of participant
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the phone number
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Get the company of participant
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Get minutes participant was in vc
     *
     * @return int
     */
    public function getMinutes()
    {
        return $this->minutes;
    }

    /**
     * Public function is known user
     *
     * @return bool
     */
    public function isKnownUser() : bool
    {
        return true;
    }

    /**
     * Get clone with minutes
     *
     * @param int 	$minutes
     *
     * @return Participant
     */
    public function withMinutes(int $minutes)
    {
        $clone = clone $this;
        $clone->minutes = $minutes;
        return $clone;
    }
}
