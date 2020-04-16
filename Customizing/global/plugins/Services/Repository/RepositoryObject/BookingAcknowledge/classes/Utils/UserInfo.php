<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Utils;

/**
 *
 */
class UserInfo
{
    /**
     * @var int
     */
    protected $id = -1;

    /**
     * @var ilObjUser | null
     */
    protected $usr_obj;

    public function withId(int $id) : UserInfo
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getId() : int
    {
        $this->checkId();
        return (int) $this->id;
    }

    public function getFirstName() : string
    {
        return $this->getUserObject()->getFirstname();
    }

    public function getLastName() : string
    {
        return $this->getUserObject()->getLastname();
    }

    public function getLogin() : string
    {
        return $this->getUserObject()->getLogin();
    }

    public function getEmail() : string
    {
        return $this->getUserObject()->getEmail();
    }

    public function getOrgu() : string
    {
        return \ilObjUser::lookupOrgUnitsRepresentation($this->getId());
    }

    protected function getUserObject()
    {
        if (!$this->usr_obj) {
            $this->usr_obj = new \ilObjUser($this->getId());
        }
        return $this->usr_obj;
    }

    protected function checkId()
    {
        if ($this->id === -1) {
            throw new LogicException("There was no id configured at UserInfo", 1);
        }
    }
}
