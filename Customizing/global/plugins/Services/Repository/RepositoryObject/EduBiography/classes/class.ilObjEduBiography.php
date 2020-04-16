<?php

require_once 'Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php';

use CaT\Plugins\EduBiography\Settings;
use CaT\Plugins\EduBiography;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

class ilObjEduBiography extends ilObjectPlugin
{
    use ilProviderObjectHelper;

    protected $settings_repository;
    protected $settings;
    protected $space;
    protected $filter;

    public function __construct($a_ref_id = 0)
    {
        global $DIC;
        $this->settings_repository = new Settings\SettingsRepository($DIC['ilDB']);
        parent::__construct($a_ref_id);

        $this->g_db = $DIC['ilDB'];
        $this->g_usr = $DIC['ilUser'];
        $this->g_ctrl = $DIC['ilCtrl'];
        $this->g_access = $DIC['ilAccess'];
        $this->g_lng = $DIC->language();
        $this->g_tree = $DIC["tree"];
    }

    public function userOrguLocator()
    {
        require_once("Services/TMS/Positions/TMSPositionHelper.php");
        require_once("Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php");
        $pos_helper = new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance());
        return new EduBiography\UserOrguLocator(ilObjOrgUnitTree::_getInstance(), $this->g_access, $pos_helper);
    }

    public function detailReport(
        $usr_id,
        EduBiography\FileStorage\ilCertificateStorage $file_storage
    ) {
        assert('is_int($usr_id)');
        return new EduBiography\DetailReport(
            $this->plugin,
            $this->g_db,
            $usr_id,
            $this->g_ctrl,
            $this->userOrguLocator(),
            $this->g_usr,
            $this->g_tree,
            $this->g_access,
            $this->settings(),
            $file_storage->withUserId($usr_id)
        );
    }
    public function detailReportSummary($usr_id)
    {
        return new EduBiography\DetailReportSummary(
            $this->plugin,
            $this->g_db,
            $usr_id,
            $this->g_lng,
            $this->settings()
        );
    }

    public function overviewReport(EduBiography\LinkGeneratorGUI $lgg)
    {
        return new EduBiography\OverviewReport(
            $this->plugin,
            $this->g_db,
            $this->g_usr,
            $this->userOrguLocator(),
            $lgg,
            $this->g_lng,
            $this->settings
        );
    }

    public function initType()
    {
        $this->setType("xebr");
    }

    public function doCreate()
    {
        $this->settings = $this->settings_repository->createSettings((int) $this->getId());
        $this->createUnboundProvider("root", EduBiography\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
    }

    public function doRead()
    {
        $this->settings = $this->settings_repository->loadSettings((int) $this->getId());
    }

    public function doUpdate()
    {
        $this->settings_repository->updateSettings($this->settings);
    }

    public function doDelete()
    {
        $this->settings_repository->deleteSettings($this->settings);
    }

    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $settings = $this->settings();
        $n_settings = $new_obj->settings();

        $n_settings = $n_settings->withIsOnline($settings->isOnline())
            ->withHasSuperiorOverview($settings->hasSuperiorOverview())
            ->withInvisibleCourseTopics($settings->getInvisibleCourseTopics())
            ->withInitVisibleColumns($settings->getInitVisibleColumns())
        ;

        $new_obj->setSettings($n_settings);
        $new_obj->update();
    }

    public function settings() : Settings\Settings
    {
        return $this->settings;
    }

    public function setSettings(Settings\Settings $settings)
    {
        $this->settings = $settings;
    }

    public function fetchData(callable $func)
    {
        return $this->report->fetchData($func);
    }

    protected function getAllInstances()
    {
        $return = [];
        foreach (self::_getObjectsByType($this->getType()) as $obj_data) {
            foreach (self::_getAllReferences($obj_data['obj_id']) as $ref_id) {
                $return[] = new static($ref_id);
            }
        }
        return $return;
    }

    /**
     * Get the values provided by this object.
     *
     * @return	string[]
     */
    public function getProvidedValues()
    {
        if ($this->checkAccess()) {
            require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EduBiography/classes/class.ilEduBiographyReportGUI.php';
            $this->g_ctrl->setParameterByClass("iledubiographyreportgui", "ref_id", $this->getRefId());
            $link = $this->g_ctrl->getLinkTargetByClass(["ilObjPluginDispatchGUI", "ilObjEduBiographyGUI", "iledubiographyreportgui"], ilEduBiographyReportGUI::CMD_VIEW);
            $this->g_ctrl->setParameterByClass("iledubiographyreportgui", "ref_id", null);
            return [[
                "title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath($this->settings->hasSuperiorOverview()),
                "active_icon_path" => $this->getActiceIconPath($this->settings->hasSuperiorOverview()),
                "identifier" => $this->getRefId()
            ]];
        }
        return [];
    }

    /**
     * Checks access to object
     *
     * @return bool
     */
    protected function checkAccess()
    {
        return $this->g_access->checkAccess('visible', '', $this->getRefId())
            && $this->g_access->checkAccess('read', '', $this->getRefId());
    }

    /**
     * Get Path of default icon
     *
     * @param bool 	$superior_icon
     *
     * @return string
     */
    protected function getIconPath($superior_icon = false)
    {
        if ($superior_icon) {
            return $this->getPlugin()->getImagePath("icon_xebr_sup.svg");
        } else {
            return $this->getPlugin()->getImagePath("icon_xebr.svg");
        }
    }

    /**
     * Get Path of active icon
     *
     * @param bool 	$superior_icon
     *
     * @return string
     */
    protected function getActiceIconPath($superior_icon = false)
    {
        if ($superior_icon) {
            return $this->getPlugin()->getImagePath("icon_xebr_sup_active.svg");
        } else {
            return $this->getPlugin()->getImagePath("icon_xebr_active.svg");
        }
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

    public function txtClosure() : Closure
    {
        return function ($code) {
            return $this->pluginTxt($code);
        };
    }

    /**
     * @return string[]
     */
    public function getAllCourseVisibleStandardUserFields() : array
    {
        return $this->getPlugin()->getAllCourseVisibleStandardUserFields();
    }

    /**
     * @return string[]
     */
    public function getAllVisibleUDFFields() : array
    {
        return $this->getPlugin()->getAllVisibleUDFFields();
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
