<?php

namespace CaT\Plugins\TrainingAssignments\AssignedTrainings;

use ILIAS\TMS\ActionBuilder;

class ilAssignedTrainingsTableGUI
{
    public function __construct(\ilAssignedTrainingsGUI $parent)
    {
        $this->parent = $parent;
        global $DIC;
        $this->g_user = $DIC->user();
        $this->g_ctrl = $DIC->ctrl();
        $this->primary = true;
    }

    /**
     * Get the html of the table
     *
     * @param UI\ViewControls[] 	$view_controls
     *
     * @return string
     */
    public function getHtml($view_controls)
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $ptable = $f->table()->presentation(
            "",
            $view_controls,
            $this->getTableClosure()
        );

        $data = $this->getData();
        return $renderer->render($ptable->withData($data));
    }

    protected function getTableClosure()
    {
        return function ($row, AssignedTraining $record, $ui_factory, $environment) {
            $actions = $record->getActions(
                ActionBuilder::CONTEXT_MY_TRAININGS,
                (int) $this->g_user->getId()
            );
            $dropdown = $this->createDropdown($ui_factory, $actions);

            return $row
                ->withHeadline($record->getTitle())
                ->withSubheadline("")
                ->withImportantFields($record->getImportantValue())
                ->withContent($ui_factory->listing()->descriptive($record->getContent()))
                ->withFurtherFields($record->getFurtherFields())
                ->withAction($dropdown);
        };
    }

    protected function createDropdown($ui_factory, array $actions)
    {
        return $ui_factory->dropdown()
            ->standard(array($ui_factory->button()->shy("Link", "")))
            ->withLabel($this->txt("actions"))
            ->withOnLoadCode(function ($id) use ($actions) {
                if (count($actions) == 0) {
                    return "$('#$id').remove();";
                }

                $content = "";
                foreach ($actions as $label => $action) {
                    $link = $action->getLink($this->g_ctrl, (int) $this->g_user->getId());
                    $content .= '<li><a class="btn btn-link" href="' . $link
                        . '" data-action="' . $link
                        . '" target="_blank">' . $action->getLabel()
                        . '</a></li>'
                    ;
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
