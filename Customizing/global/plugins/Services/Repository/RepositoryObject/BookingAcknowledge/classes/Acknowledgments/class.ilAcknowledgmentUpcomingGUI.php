<?php

declare(strict_types=1);

use CaT\Plugins\BookingAcknowledge\Acknowledgments\AcknowledgmentGUI;
use CaT\Plugins\BookingAcknowledge\Utils\RequestDigester;

/**
 * GUI for an overview of booking requests.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilAcknowledgmentUpcomingGUI extends AcknowledgmentGUI
{
    protected function getConfirmationGUI()
    {
        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
        return $confirmation;
    }

    protected function getConfirmationHeaderText(string $cmd) : string
    {
        if ($cmd === RequestDigester::CMD_ACKNOWLEDGE) {
            $msg = "msg_confirm_acknowledge";
        } elseif ($cmd === RequestDigester::CMD_DECLINE) {
            $msg = "msg_confirm_decline";
        } else {
            throw new \InvalidArgumentException("Error Processing Request", 1);
        }
        return $this->txt($msg);
    }


    protected function confirm(string $ok_command, array $usrcrs)
    {
        $confirmation = $this->getConfirmationGUI();
        $confirmation->setHeaderText($this->getConfirmationHeaderText($ok_command));
        $confirmation->setConfirm($this->txt("confirm"), $ok_command);
        $confirmation->setCancel($this->txt("confirm_cancel"), AcknowledgmentGUI::CMD_SHOW_UPCOMING);

        foreach ($usrcrs as $entry) {
            $id = $this->digester->prepare($entry->getUserId(), $entry->getCourseRefId());
            $usr_info = $this->object->getUserInfo($entry->getUserId());
            $crs_info = $this->object->getCourseInfo($entry->getCourseRefId());

            $item_txt = implode(', ', [
                $crs_info->getTitle(),
                $usr_info->getLastName(),
                $usr_info->getFirstName(),
                $usr_info->getOrgu(),
                $crs_info->getCourseType(),
                $crs_info->getCourseDates()
            ]);

            $confirmation->addItem(
                $this->digester->getUsrCrsParameter() . '[]',
                $id,
                $item_txt
            );
        }

        $this->addGivenReportFiltersToConfirmForm($confirmation);
        $this->g_tpl->setContent($confirmation->getHtml());
    }

    protected function addGivenReportFiltersToConfirmForm(\ilConfirmationGUI $confirmation)
    {
        $confirmation->addHiddenItem(
            RequestDigester::F_FILTER,
            $_GET[RequestDigester::F_FILTER],
            'filter'
        );
    }
}
