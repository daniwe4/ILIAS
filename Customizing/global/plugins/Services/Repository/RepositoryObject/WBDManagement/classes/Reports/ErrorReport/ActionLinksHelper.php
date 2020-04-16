<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDManagement\Reports\ErrorReport;

require_once __DIR__ . "/class.ilWBDReportGUI.php";

/**
 * Creates action links for request error table
 */
class ActionLinksHelper
{
    /**
     * @var string
     */
    protected $target_class;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var int
     */
    protected $re_id = null;

    public function __construct(string $target_class, \ilCtrl $ctrl)
    {
        $this->target_class = $target_class;
        $this->ctrl = $ctrl;
    }

    public function getResolveLinkFor(int $re_id)
    {
        $this->ctrl->setParameterByClass($this->target_class, \ilWBDReportGUI::P_IDS_TO_HANDLE, $re_id);
        $link = $this->ctrl->getLinkTargetByClass($this->target_class, \ilWBDReportGUI::CMD_CONFIRM_STATUS_RESOLVED);
        $this->ctrl->setParameterByClass($this->target_class, \ilWBDReportGUI::P_IDS_TO_HANDLE, null);

        return $link;
    }

    public function getNotResolvableLinkFor(int $re_id)
    {
        $this->ctrl->setParameterByClass($this->target_class, \ilWBDReportGUI::P_IDS_TO_HANDLE, $re_id);
        $link = $this->ctrl->getLinkTargetByClass($this->target_class, \ilWBDReportGUI::CMD_CONFIRM_STATUS_NOT_RESOLVABLE);
        $this->ctrl->setParameterByClass($this->target_class, \ilWBDReportGUI::P_IDS_TO_HANDLE, null);

        return $link;
    }
}
