<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Settings;

/**
 * This is the object for additional settings.
 *
 * @author  Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class BookingApprovals
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $superior_view;

    public function __construct(int $obj_id, bool $superior_view = false)
    {
        $this->obj_id = $obj_id;
        $this->superior_view = $superior_view;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getSuperiorView() : bool
    {
        return $this->superior_view;
    }

    public function withSuperiorView(bool $superior_view) : BookingApprovals
    {
        $clone = clone $this;
        $clone->superior_view = $superior_view;
        return $clone;
    }
}
