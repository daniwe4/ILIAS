<?php

declare(strict_types=1);

namespace CaT\Plugins\CopySettings\Settings;

class ilActions
{

    /**
     * @var \ilObjCopySettings
     */
    protected $object;

    /**
     * @var DB
     */
    protected $settings_db;

    public function __construct(\ilObjCopySettings $object, DB $settings_db)
    {
        $this->object = $object;
        $this->settings_db = $settings_db;
    }

    public function create() : Settings
    {
        return $this->settings_db->create((int) $this->getObject()->getId());
    }

    public function update(Settings $settings)
    {
        $this->settings_db->update($settings);
    }

    public function select() : Settings
    {
        return $this->settings_db->select((int) $this->getObject()->getId());
    }

    public function delete()
    {
        $this->settings_db->delete((int) $this->getObject()->getId());
    }

    public function getObject() : \ilObjCopySettings
    {
        if ($this->object === null) {
            throw new \Exception("No object set");
        }

        return $this->object;
    }
}
