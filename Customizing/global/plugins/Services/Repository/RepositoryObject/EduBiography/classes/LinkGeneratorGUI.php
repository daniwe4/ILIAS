<?php
declare(strict_types = 1);

namespace CaT\Plugins\EduBiography;

require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EduBiography/classes/class.ilEduBiographyReportGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EduBiography/classes/class.ilObjEduBiographyGUI.php';

class LinkGeneratorGUI
{
    protected $ctrl;
    protected $rgui;
    protected $parameters = [];

    public function __construct(\ilCtrl $ctrl)
    {
        $this->ctrl = $ctrl;
    }

    /**
     * This hack is currently necessary to avoid filter forwarding to
     * detail gui.
     */
    public function setReportGui(\ilEduBiographyReportGUI $rgui)
    {
        $this->rgui = $rgui;
    }

    /**
     * Add custom parameters to the link
     */
    public function addParameter(string $key, string $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * This method serves to avoid ctrl handling within an object and still being able to create links
     * for detail reports.
     *
     * @param	int	$usr_id
     * @return	string
     */
    public function getLinkForUserDetailReport(int $usr_id) : string
    {
        $this->rgui->disableRelevantParametersCtrl();
        $this->ctrl->setParameterByClass('ilEduBiographyReportGUI', \ilObjEduBiographyGUI::GET_TARGET_USR_ID, $usr_id);
        foreach ($this->parameters as $key => $value) {
            $this->ctrl->setParameterByClass('ilEduBiographyReportGUI', $key, $value);
        }
        $return = $this->ctrl->getLinkTargetByClass(['ilObjPluginDispatchGUI','ilObjEduBiographyGUI','ilEduBiographyReportGUI'], \ilEduBiographyReportGUI::CMD_VIEW);
        $this->ctrl->setParameterByClass('ilEduBiographyReportGUI', \ilObjEduBiographyGUI::GET_TARGET_USR_ID, null);
        foreach ($this->parameters as $key => $value) {
            $this->ctrl->setParameterByClass('ilEduBiographyReportGUI', $key, null);
        }
        $this->rgui->enableRelevantParametersCtrl();
        return $return;
    }

    public function redirectToDetailReport(int $usr_id)
    {
        $this->rgui->disableRelevantParametersCtrl();
        $this->ctrl->setParameterByClass('ilEduBiographyReportGUI', \ilObjEduBiographyGUI::GET_TARGET_USR_ID, $usr_id);
        foreach ($this->parameters as $key => $value) {
            $this->ctrl->setParameterByClass('ilEduBiographyReportGUI', $key, $value);
        }
        $this->ctrl->redirectByClass(['ilObjPluginDispatchGUI','ilObjEduBiographyGUI','ilEduBiographyReportGUI'], \ilEduBiographyReportGUI::CMD_VIEW);
        die();
    }
}
