<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\OverviewCertificate;

use ILIAS\UI;

class ilCertificateTableGUI
{
    /**
     * @var UI\Factory
     */
    protected $factory;

    /**
     * @var UI\Renderer
     */
    protected $renderer;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    public function __construct(
        UI\Factory $factory,
        UI\Renderer $renderer,
        \Closure $txt,
        \ilObjUser $user,
        \ilCtrl $ctrl
    ) {
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->txt = $txt;
        $this->user = $user;
        $this->ctrl = $ctrl;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function render() : string
    {
        $ptable = $this->factory->table()->presentation(
            $this->txt("certificate_header"), //title
            [],
            function ($row, Certificate $record, $ui_factory, $environment) {
                $row = $row
                    ->withHeadline($record->getTitle())
                    ->withImportantFields([])
                    ->withFurtherFields([])
                ;

                $value = [];
                $start = \ilDatePresentation::formatDate(
                    new \ilDate(
                        $record->getStart()->format("Y-m-d"),
                        IL_CAL_DATE
                    )
                );
                $end = \ilDatePresentation::formatDate(
                    new \ilDate(
                        $record->getEnd()->format("Y-m-d"),
                        IL_CAL_DATE
                    )
                );
                $value[$this->txt("period")] = $ui_factory->listing()->unordered([
                    $start
                    . " - "
                    . $end
                ]);
                $value[$this->txt("min_idd_value")] = $ui_factory->listing()->unordered(
                    [
                        $this->minutesToTimeString($record->getMinIddValue()) . " " . $this->txt("hours")
                    ]
                );
                $value[$this->txt("received_idd_minutes")] = $ui_factory->listing()->unordered(
                    [
                        $this->minutesToTimeString($record->getReceivedIddMinutes()) . " " . $this->txt("hours")
                    ]
                );
                $row = $row->withContent($ui_factory->listing()->descriptive($value));

                $row = $row->withAction($this->createDropDown(
                    $ui_factory,
                    $record->getId(),
                    $record->getMinIddValue(),
                    $record->getReceivedIddMinutes(),
                    $record->isPartDocument(),
                    $record->isShowOverviewDownload()
                ));

                return $row;
            }
        );

        return $this->renderer->render($ptable->withData($this->getData()));
    }

    protected function createDropDown(
        UI\Factory $ui_factory,
        int $id,
        int $min_idd_value,
        int $received_idd_min,
        bool $part_document,
        bool $show_overview_download
    ) : UI\Component\Dropdown\Dropdown {
        $btn = [];

        if (
            $show_overview_download &&
            $received_idd_min >= $min_idd_value
        ) {
            $btn[] = $this->getCertificateButton($ui_factory, $id, $received_idd_min);
        }

        if ($part_document) {
            $btn[] = $this->getPartDocument($ui_factory, $id, $received_idd_min);
        }

        return $ui_factory->dropdown()->standard($btn)
            ->withLabel($this->txt("actions"))
            ;
    }

    protected function getCertificateButton(
        UI\Factory $ui_factory,
        int $id,
        int $received_idd_min
    ) : UI\Component\Button\Button {
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::SCHEDULE_ID, $id);
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::RECEIVED_IDD_MIN, $received_idd_min);
        $link = $this->ctrl->getLinkTargetByClass(
            \ilCertificateDownloadGUI::class,
            \ilCertificateDownloadGUI::CMD_DOWNLOAD_CERTIFICATE,
            "",
            true,
            false
        );
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::SCHEDULE_ID, null);
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::RECEIVED_IDD_MIN, null);

        return $ui_factory->button()->primary(
            $this->txt("certificate_download"),
            $link
        );
    }

    protected function getPartDocument(
        UI\Factory $ui_factory,
        int $id
    ) : UI\Component\Button\Button {
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::SCHEDULE_ID, $id);
        $link = $this->ctrl->getLinkTargetByClass(
            \ilCertificateDownloadGUI::class,
            \ilCertificateDownloadGUI::CMD_DOWNLOAD_PART_DOCUMENT,
            "",
            true,
            false
        );
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::SCHEDULE_ID, null);

        return $ui_factory->button()->primary(
            $this->txt("part_document_download"),
            $link
        );
    }

    protected function minutesToTimeString(int $minutes)
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;

        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT)
            . ':'
            . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT)
            ;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
