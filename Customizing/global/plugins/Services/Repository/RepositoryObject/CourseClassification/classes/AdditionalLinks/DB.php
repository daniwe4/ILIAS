<?php
declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\AdditionalLinks;

interface DB
{

    /**
     * @return AdditionalLink[]
     */
    public function selectFor(int $obj_id) : array;

    public function deleteFor(int $obj_id);

    /**
     * @param int $obj_id
     * @param AdditionalLink[] $links
     */
    public function storeFor(int $obj_id, array $links);
}
