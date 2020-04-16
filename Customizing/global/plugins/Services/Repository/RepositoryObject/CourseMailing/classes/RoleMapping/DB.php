<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMailing\RoleMapping;

interface DB
{
    public function upsert(RoleMapping $mapping);
    public function selectForObjectId(int $obj_id) : array;
    public function deleteForObject(int $obj_id);
    public function selectFor(int $id) : RoleMapping;
    public function deleteFor(int $id);
    public function getMappingObject(
        int $mapping_id,
        int $obj_id,
        int $role_id,
        int $template_id = null,
        array $attachments = []
    ) : RoleMapping;
    public function deleteRoleEntriesById(int $role_id);
}
