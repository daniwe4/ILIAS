<?php declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

trait RepositoryPluginLocatorTrait
{
    public function lookupTypeByRefId(int $ref_id) : string
    {
        return \ilObject::_lookupType($ref_id, true);
    }

    public function loadPluginForType(string $type) : \ilRepositoryObjectPlugin
    {
        if (!\ilPluginAdmin::isPluginActive($type)) {
            throw new \InvalidArgumentException($type . ' seems to be inactive');
        }
        return \ilPluginAdmin::getPluginObjectById($type);
    }
}
