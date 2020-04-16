<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\TrainingAdminOverview;
use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Plugins\TrainingAdminOverview\Settings\Settings;

/**
 * Object of the plugin
 */
class ilObjTrainingAdminOverview extends ilObjectPlugin
{
    use ilProviderObjectHelper;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var TrainingAdminOverview\Settings\ilDB;
     */
    protected $settings_db;


    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);

        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->db = $DIC->database();
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xado");
    }

    /**
     * Creates ente-provider.
     */
    public function doCreate()
    {
        $obj_id = (int) $this->getId();
        $this->settings = $this->getSettingsDB()->createSettings($obj_id);
        $this->createUnboundProvider(
            "root",
            TrainingAdminOverview\UnboundProvider::class,
            __DIR__ . "/UnboundProvider.php"
        );
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->getSettingsDB()->updateSettings($this->settings);
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $obj_id = (int) $this->getId();
        $this->settings = $this->getSettingsDB()->selectByObjId($obj_id);
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $obj_id = (int) $this->getId();
        $this->getSettingsDB()->delete($obj_id);
        $this->deleteUnboundProviders();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $src = $this->getSettings();
        $fnc = function ($settings) use ($src) {
            return $settings->withShowInfoTab($src->getShowInfoTab());
        };
        $new_obj->updateSettings($fnc);
        $new_obj->update();
    }

    public function updateSettings(\Closure $c)
    {
        $this->settings = $c($this->settings);
    }

    public function getSettings() : Settings
    {
        return $this->settings;
    }

    public function getSettingsDB()
    {
        if (is_null($this->settings_db)) {
            $this->settings_db = new CaT\Plugins\TrainingAdminOverview\Settings\ilDB($this->db);
        }
        return $this->settings_db;
    }

    /**
     * Get the actions of object
     *
     * @return ilObjActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $this->actions = new TrainingAdminOverview\ilObjActions(
                $this,
                $this->getAdministratedTrainingsDB()
            );
        }

        return $this->actions;
    }

    /**
     * Get instance of booking db
     *
     * @return TrainingAdminOverview\AdministratedTrainings\DB
     */
    protected function getAdministratedTrainingsDB()
    {
        if ($this->administrated_trainings_db === null) {
            global $DIC;
            $db = $DIC->database();
            $access = $DIC->access();
            $tree = $DIC->repositoryTree();
            $objDefinition = $DIC["objDefinition"];
            $this->administrated_trainings_db = new TrainingAdminOverview\AdministratedTrainings\ilDB($db, $access, $tree, $objDefinition);
        }

        return $this->administrated_trainings_db;
    }

    /**
     * Get the values provided by this object.
     *
     * @return	string[]
     */
    public function getProvidedValues()
    {
        $returns = array();

        if ($this->access->checkAccess("visible", '', $this->getRefId())
            && $this->access->checkAccess("read", '', $this->getRefId())
        ) {
            require_once(__DIR__ . "/AdministratedTrainings/class.ilAdministratedTrainingsGUI.php");
            $this->ctrl->setParameterByClass("ilAdministratedTrainingsGUI", "ref_id", $this->getRefId());
            $link = $this->ctrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI", "ilObjTrainingAdminOverviewGUI", "ilAdministratedTrainingsGUI"), ilAdministratedTrainingsGUI::CMD_SHOW_ASSIGNMENTS);
            $this->ctrl->setParameterByClass("ilAdministratedTrainingsGUI", "ref_id", null);

            $returns[] = ["title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath(),
                "active_icon_path" => $this->getActiceIconPath(),
                "identifier" => $this->getRefId()
            ];
        }

        return $returns;
    }

    /**
     * Get Path of default icon
     *
     * @return string
     */
    protected function getIconPath()
    {
        return $this->getPlugin()->getImagePath("icon_xado.svg");
    }

    /**
     * Get Path of active icon
     *
     * @return string
     */
    protected function getActiceIconPath()
    {
        return $this->getPlugin()->getImagePath("icon_xado_active.svg");
    }

    /**
     * Translate lang code into value
     *
     * @param string 	$code
     *
     * @return string
     */
    public function pluginTxt($code)
    {
        return parent::txt($code);
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }
}
