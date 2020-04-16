<?php

namespace CaT\Plugins\UserBookings\SuperiorView;

use CaT\Plugins\UserBookings\Helper;
use ILIAS\TMS\ActionBuilder;

class ilSuperiorViewTableGUI
{
    /**
     * @var mixed[]
     */
    protected $data;

    public function __construct(\ilSuperiorViewGUI $parent, Helper $helper, array $view_controls, $table_mode)
    {
        $this->parent = $parent;
        $this->helper = $helper;
        $this->view_controls = $view_controls;
        $this->table_mode = $table_mode;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->primary = true;
    }

    public function getHtml(bool $with_recommendation_action)
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        //build table
        $ptable = $f->table()->presentation(
            "", //title
            $this->view_controls,
            $this->getClosureAccordingToMode($with_recommendation_action)
        );

        $data = $this->getData();

        //apply data to table and render
        return $renderer->render($ptable->withData($data));
    }

    protected function getClosureAccordingToMode(bool $with_recommendation_action)
    {
        switch ($this->table_mode) {
            case \ilSuperiorViewGUI::T_USER:
                return $this->getUserDrivenView($with_recommendation_action);
            case \ilSuperiorViewGUI::T_COURSE:
                return $this->getCourseDrivenView($with_recommendation_action);
            default:
                throw new \Exception("Unknown table mode: " . $this->table_mode);
        }
    }

    protected function getCourseDrivenView(bool $with_recommendation_action)
    {
        return function ($row, UserBooking $record, $ui_factory, $environment) use ($with_recommendation_action) {
            $actions = $record->getActions(
                ActionBuilder::CONTEXT_EMPLOYEE_BOOKING,
                $record->getUserId(),
                $with_recommendation_action
            );
            $dropdown = $this->createDropdown($ui_factory, $actions, $record->getUserId());

            return $row
                ->withHeadline($record->getTitleValue())
                ->withSubheadline($record->getFullName())
                ->withImportantFields($record->getImportantFields())
                ->withContent($ui_factory->listing()->descriptive($record->getDetailFields()))
                ->withFurtherFields($record->getFurtherFields())
                ->withAction($dropdown);
        };
    }

    protected function getUserDrivenView(bool $with_recommendation_action)
    {
        return function ($row, UserBooking $record, $ui_factory, $environment) use ($with_recommendation_action) {
            $actions = $record->getActions(
                ActionBuilder::CONTEXT_EMPLOYEE_BOOKING,
                $record->getUserId(),
                $with_recommendation_action
            );
            $dropdown = $this->createDropdown($ui_factory, $actions, $record->getUserId());

            return $row
                ->withHeadline($record->getFullName())
                ->withSubheadline($record->getTitleValue())
                ->withImportantFields($record->getImportantFields())
                ->withContent($ui_factory->listing()->descriptive($record->getDetailFields()))
                ->withFurtherFields($record->getFurtherFields())
                ->withAction($dropdown);
        };
    }

    protected function createDropdown($ui_factory, array $actions, $record_usr_id)
    {
        return $ui_factory->dropdown()
            ->standard(array($ui_factory->button()->shy("Link", "")))
            ->withLabel($this->txt("actions"))
            ->withOnLoadCode(function ($id) use ($actions, $record_usr_id) {
                if (count($actions) == 0) {
                    return "$('#$id').remove();";
                }

                $content = "";
                foreach ($actions as $action) {
                    $link = $action->getLink($this->g_ctrl, $record_usr_id);
                    $label = $action->getLabel();
                    $target = "";

                    if ($action->openInNewTab()) {
                        $target = "target=\"_blank\"";
                    }

                    $content .= '<li><a class="btn btn-link" href="' . $link . '" data-action="' . $link . '" ' . $target . '>' . $label . '</a></li>';
                }
                return "$('#$id + ul').empty(); $('#$id + ul').append('$content');";
            });
    }

    protected function txt($cmd)
    {
        return $this->parent->txt($cmd);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    protected function getData()
    {
        return $this->data;
    }
}
