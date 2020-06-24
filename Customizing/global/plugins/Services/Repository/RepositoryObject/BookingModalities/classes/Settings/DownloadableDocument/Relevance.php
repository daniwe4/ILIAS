<?php

namespace CaT\Plugins\BookingModalities\Settings\DownloadableDocument;

/**
 * Keeps information of role-assignments for docs
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class Relevance
{

    /**
     * @var int
     */
    protected $role_id;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param int 	$role_id
     * @param string|null 	$filename
     */
    public function __construct(int $role_id, ?string $filename = null)
    {
        $this->role_id = $role_id;
        $this->filename = $filename;
    }


    /**
     * Get the role id
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * Get the associated filename
     *
     * @return string|null
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * Get a copy of this with a filename as set
     *
     * @return Relevance
     */
    public function withFileName(string $filename)
    {
        $clone = clone $this;
        $clone->filename = $filename;
        return $clone;
    }
}
