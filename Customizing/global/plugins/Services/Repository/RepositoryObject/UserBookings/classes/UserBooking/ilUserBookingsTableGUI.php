<?php

declare(strict_types=1);

namespace CaT\Plugins\UserBookings\UserBooking;

use CaT\Plugins\UserBookings\Helper;
use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\CourseAction;

class ilUserBookingsTableGUI
{
    /**
     * @var mixed[]
     */
    protected $data;

    public function __construct(\ilUserBookingsGUI $parent, Helper $helper, $search_user_id, array $view_controls)
    {
        $this->parent = $parent;
        $this->helper = $helper;
        $this->primary = true;
        $this->view_controls = $view_controls;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->search_user_id = $search_user_id;
    }

    public function getHtml(
        int $offset,
        int $limit,
        bool $with_recommendation_action
    ) {
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        //build table
        $ptable = $f->table()->presentation(
            "", //title
            $this->view_controls,
            function ($row, UserBooking $record, $ui_factory, $environment) use ($with_recommendation_action) {
                $actions = $record->getActions(
                    ActionBuilder::CONTEXT_USER_BOOKING,
                    (int) $this->search_user_id,
                    $with_recommendation_action
                );
                $dropdown = $this->createDropdown(
                    $ui_factory,
                    $actions
                );

                return $row
                    ->withHeadline($record->getTitleValue())
                    ->withSubheadline($record->getSubTitleValue())
                    ->withImportantFields($record->getImportantFields())
                    ->withContent($ui_factory->listing()->descriptive($record->getDetailFields()))
                    ->withFurtherFields($record->getFurtherFields())
                    ->withAction($dropdown);
            }
        );

        $data = array_slice($this->getData(), $offset, $limit);

        //apply data to table and render
        return $renderer->render($ptable->withData($data));
    }

    protected function createDropdown($ui_factory, $actions)
    {
        return $ui_factory->dropdown()
            ->standard(array($ui_factory->button()->shy("Link", "")))
            ->withLabel($this->txt("actions"))
            ->withOnLoadCode(function ($id) use ($actions) {
                if (count($actions) == 0) {
                    return "$('#$id').remove();";
                }

                $content = "";
                foreach ($actions as $action) {
                    $link = $action->getLink($this->g_ctrl, $this->search_user_id);
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
