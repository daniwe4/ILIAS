<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Repository/classes/class.ilObjectPlugin.php";

use CaT\Plugins\WBDManagement;
use CaT\Plugins\WBDManagement\DI;
use CaT\Plugins\WBDManagement\Settings;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

class ilObjWBDManagement extends ilObjectPlugin //implements ObjWBDManagement
{
    use DI;
    use ilProviderObjectHelper;

    /**
     * @var \Pimple\Container
     */
    protected $dic;

    /**
     * @var Settings\WBDManagement
     */
    protected $settings;

    /**
     * @param ilObjWBDManagement $new_obj
     * @param int|string $a_target_id
     * @param int|string|null $a_copy_id
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $n_document_path = $new_obj
            ->getFileStorage()
            ->copyFileFrom(
                $this->getSettings()->getDocumentPath()
            )
        ;

        $func = function (Settings\WBDManagement $s) use ($n_document_path) {
            return $s
                ->withOnline($this->getSettings()->isOnline())
                ->withShowInCockpit($this->getSettings()->isShowInCockpit())
                ->withDocumentPath($n_document_path)
                ->withEmail($this->getSettings()->getEmail())
            ;
        };

        $new_obj->updateSettings($func);
        $new_obj->update();

        if ($this->getSettings()->isShowInCockpit()) {
            $new_obj->createProvider();
        }
    }

    public function doCreate()
    {
        $this->settings = $this->getSettingsDB()->create((int) $this->getId());
    }

    public function doDelete()
    {
        $this->getSettingsDB()->deleteFor((int) $this->getId());
        $this->deleteProvider();
    }

    public function doRead()
    {
        $this->settings = $this->getSettingsDB()->selectFor((int) $this->getId());
    }

    public function doUpdate()
    {
        $this->getSettingsDB()->update($this->settings);
    }

    protected function getDI() : \Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getObjectDIC($this, $DIC);
        }
        return $this->dic;
    }

    /**
     * Get the values provided by this object.
     *
     * @return	string[]
     */
    public function getProvidedValues()
    {
        $returns = array();

        if (
            $this->getSettings()->isShowInCockpit() &&
            $this->getDI()["ilAccess"]->checkAccess("visible", "", $this->getRefId()) &&
            $this->getDI()["ilAccess"]->checkAccess("read", "", $this->getRefId())
        ) {
            $this->getDI()["ilCtrl"]->setParameterByClass("ilgutberatengui", "ref_id", $this->getRefId());
            $link = $this->getDI()["gut.beraten.gui.link"];
            $this->getDI()["ilCtrl"]->setParameterByClass("ilgutberatengui", "ref_id", null);

            $returns[] = ["title" => html_entity_decode($this->getTitle()),
                          "tooltip" => html_entity_decode($this->getDescription()),
                          "link" => $link,
                          "icon_path" => $this->getIconPath(),
                          "active_icon_path" => $this->getIconPath(),
                          "identifier" => $this->getRefId()
            ];
        }

        return $returns;
    }

    protected function getIconPath() : string
    {
        return $this->getPlugin()->getImagePath("icon_xwbm.svg");
    }

    public function initType()
    {
        $this->setType("xwbm");
    }

    public function txtClosure() : \Closure
    {
        return function (string $code) {
            return $this->txt($code);
        };
    }

    public function getSettings() : Settings\WBDManagement
    {
        return $this->settings;
    }

    public function updateSettings(\Closure $c)
    {
        $this->settings = $c($this->getSettings());
    }

    protected function getSettingsDB() : Settings\DB
    {
        return $this->getDI()["settings.db"];
    }

    public function createProvider()
    {
        $this->createUnboundProvider(
            "root",
            WBDManagement\UnboundProvider::class,
            __DIR__ . "/UnboundProvider.php"
        );
    }

    public function deleteProvider()
    {
        $this->deleteUnboundProviders();
    }

    public function getPluginDirectory() : string
    {
        return $this->plugin->getDirectory();
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    public function getPluginDir() : string
    {
        return $this->plugin->getDirectory();
    }

    protected function getFileStorage()
    {
        return $this->getDI()["settings.file.storage"];
    }
}
