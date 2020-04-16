<?php

declare(strict_types=1);

use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Plugins\TrainerOperations\UnboundProvider;
use CaT\Plugins\TrainerOperations\ObjTrainerOperations;
use CaT\Plugins\TrainerOperations\Settings\Settings;
use CaT\Plugins\TrainerOperations\AccessHelper;
use CaT\Plugins\TrainerOperations\DI;

/**
 * Object of the plugin.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilObjTrainerOperations extends ilObjectPlugin implements ObjTrainerOperations
{
    use DI;
    use ilProviderObjectHelper;

    /**
     * @var ArrayAccess
     */
    protected $dic;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ArrayAccess
     */
    protected $di;


    public function __construct(int $ref_id = 0)
    {
        parent::__construct($ref_id);

        global $DIC;
        $this->dic = $DIC;
        $this->g_ctrl = $DIC['ilCtrl'];
    }

    public function doCreate()
    {
        $this->createUnboundProvider("root", UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $settings_db = $this->getDI()['db.settings'];
        $settings_db->createEmpty((int) $this->getId(), []);
    }

    public function doDelete()
    {
        $settings_db = $this->getDI()['db.settings'];
        $this->settings = $settings_db->deleteFor((int) $this->getId());
        $user_settings_db = $this->getDI()['db.usersettings'];
        $user_settings_db->deleteFor((int) $this->getId());
    }

    public function doRead()
    {
        $settings_db = $this->getDI()['db.settings'];
        $this->settings = $settings_db->selectFor((int) $this->getId());
    }

    public function doUpdate()
    {
        $settings_db = $this->getDI()['db.settings'];
        $settings_db->update($this->getSettings());
    }


    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $settings = $this->getSettings();
        $new_obj->doRead();

        $nu_obj_settings = $new_obj->getSettings()
            ->withGlobalRoles($settings->getGlobalRoles());

        $settings_db = $this->getDI()['db.settings'];
        $settings_db->update($nu_obj_settings);
    }


    public function getSettings() : Settings
    {
        return $this->settings;
    }

    public function updateSettings(\Closure $c)
    {
        $this->settings = $c($this->settings);
    }

    public function initType()
    {
        $this->setType(ObjTrainerOperations::PLUGIN_ID);
    }

    protected function getDIC() : \ArrayAccess
    {
        return $this->dic;
    }

    protected function getIconPath() : string
    {
        return $this->getPlugin()->getImagePath("icon_xtep.svg");
    }

    protected function getActiceIconPath() : string
    {
        return $this->getPlugin()->getImagePath("icon_xtep.svg");
    }

    public function getTxtClosure() : Closure
    {
        return function ($code) {
            return $this->plugin->txt($code);
        };
    }

    public function getDI() : \ArrayAccess
    {
        if (is_null($this->di)) {
            $this->di = $this->getObjectDI(
                $this,
                $this->getDIC(),
                $this->getTxtClosure()
            );
        }
        return $this->di;
    }

    public function getProvidedValues() : array
    {
        $returns = array();
        $access_helper = $this->getDI()["utils.access"];

        if ($access_helper->maySeeCockpitItem()) {
            require_once('class.ilObjTrainerOperationsGUI.php');
            $this->g_ctrl->setParameterByClass("ilObjTrainerOperationsGUI", "ref_id", $this->getRefId());
            $link = $this->g_ctrl->getLinkTargetByClass(
                array("ilObjPluginDispatchGUI", "ilObjTrainerOperationsGUI"),
                ilTrainerOperationsGUI::CMD_SHOW
            );

            $returns[] = [
                "title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath(true),
                "active_icon_path" => $this->getActiceIconPath(true),
                "identifier" => $this->getRefId()
            ];
        }

        return $returns;
    }
}
