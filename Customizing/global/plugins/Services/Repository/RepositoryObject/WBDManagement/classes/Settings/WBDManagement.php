<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Settings;

class WBDManagement
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $online;

    /**
     * @var string
     */
    protected $document_path;

    /**
     * @var string
     */
    protected $email;

    public function __construct(
        int $obj_id,
        bool $online = false,
        string $document_path = null,
        string $email = null
    ) {
        $this->obj_id = $obj_id;
        $this->online = $online;
        $this->document_path = $document_path;
        $this->email = $email;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function isOnline() : bool
    {
        return $this->online;
    }

    public function withOnline(bool $online) : WBDManagement
    {
        $clone = clone $this;
        $clone->online = $online;
        return $clone;
    }

    public function getDocumentPath()
    {
        return $this->document_path;
    }

    public function withDocumentPath(string $document_path = null) : WBDManagement
    {
        $clone = clone $this;
        $clone->document_path = $document_path;
        return $clone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function withEmail(string $email = null) : WBDManagement
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }
}
