<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

use CaT\Plugins\TrainingSearch\Settings\Settings;
use ILIAS\TMS\CourseAction;
use ILIAS\TMS\ActionBuilder;
use ILIAS\UI;

/**
 * Table gui to present bookable courses
 */
class ilCoursesTableGUI
{
    /**
     * @var UI\Implementation\Component\Modal\Modal[]
     */
    protected $modals;

    public function __construct(
        \ilCtrl $ctrl,
        \ilObjUser $user,
        UI\Factory $factory,
        UI\Renderer $renderer,
        Settings $settings,
        \Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->user = $user;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->settings = $settings;
        $this->txt = $txt;

        $this->primary = true;
        $this->modals = [];
    }

    /**
     * Set data to show in table
     *
     * @param mixed[] 	$data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get data should me shown in table
     *
     * @return mixed[]
     */
    public function getData() : array
    {
        return $this->data;
    }

    public function render(
        array $view_constrols,
        int $offset = 0,
        int $limit = null,
        int $search_user_id,
        bool $with_recommendation_action
    ) : string {
        $ptable = $this->factory->table()->presentation(
            $this->txt("header"), //title
            $view_constrols,
            function ($row, Course $record, $ui_factory, $environment) use ($search_user_id, $with_recommendation_action) {
                $row = $row
                    ->withHeadline($record->getTitleValue())
                    ->withSubheadline($record->getSubTitleValue())
                    ->withImportantFields($record->getImportantFields())
                    ->withContent($ui_factory->listing()->descriptive($record->getDetailFields()))
                    ->withFurtherFields($record->getFurtherFields());

                $is_superior = $search_user_id != $this->user->getId();

                if ($is_superior) {
                    $search_actions = $record->getActions(
                        ActionBuilder::CONTEXT_SUPERIOR_SEARCH,
                        $search_user_id,
                        $with_recommendation_action
                    );
                } else {
                    $search_actions = $record->getActions(
                        ActionBuilder::CONTEXT_SEARCH,
                        $search_user_id,
                        $with_recommendation_action
                    );
                }

                if (count($search_actions) > 0) {
                    if (count($search_actions) == 1
                        && $search_user_id == ANONYMOUS_USER_ID
                    ) {
                        $sa = array_shift($search_actions);
                        if ($sa->hasModal()) {
                            $modal = $sa->getModal($this->ctrl, $ui_factory, $search_user_id);
                            $action = $this->createButtonForModal(
                                $ui_factory,
                                $sa,
                                $modal
                            );
                            $this->modals[] = $modal;
                        } else {
                            $action = $this->createButton(
                                $ui_factory,
                                $sa,
                                $search_user_id
                            );
                        }
                    } elseif (!$with_recommendation_action) {
                        $sa = array_shift($search_actions);
                        $action = $this->createButton(
                            $ui_factory,
                            $sa,
                            $search_user_id
                        );
                    } else {
                        $action = $this->createDropdown(
                            $record,
                            $ui_factory,
                            $search_user_id,
                            $search_actions
                        );
                    }
                }

                if (!is_null($action)) {
                    $row = $row->withAction($action);
                }

                return $row;
            }
        );

        $data = array_slice($this->getData(), $offset, $limit);

        //apply data to table and render
        $ret = $this->renderer->render($ptable->withData($data))
            . $this->renderer->render($this->modals);

        return $ret;
    }

    /**
     * Create an ui button
     *
     * @param string 	$link
     *
     * @return Button
     */
    protected function createDropdown($record, $ui_factory, $search_user_id, $search_actions)
    {
        return $ui_factory->dropdown()
            ->standard(array($ui_factory->button()->shy("Link", "")))
            ->withLabel($this->txt("actions"))
            ->withOnLoadCode(function ($id) use ($record, $search_user_id, $search_actions) {
                if (count($search_actions) == 0) {
                    return "$('#$id').remove();";
                }

                $content = "";
                foreach ($search_actions as $action) {
                    $link = $action->getLink($this->ctrl, $search_user_id);
                    $label = $action->getLabel();

                    $content .= '<li><a class="btn btn-link" href="' . $link . '" data-action="' . $link . '">' . $label . '</a></li>';
                }
                return "$('#$id + ul').empty(); $('#$id + ul').append('$content');";
            });
    }

    protected function createButton(
        UI\Factory $ui_factory,
        $search_action,
        $search_user_id
    ) : UI\Implementation\Component\Button\Button {
        $link = $search_action->getLink($this->ctrl, $search_user_id);
        $label = $search_action->getLabel();

        return $ui_factory->button()->primary(
            $label,
            $link
        );
    }

    protected function createButtonForModal(
        UI\Factory $ui_factory,
        $search_action,
        $modal
    ) : UI\Implementation\Component\Button\Button {
        $label = $search_action->getLabel();

        return $ui_factory->button()->standard($label, '#')
            ->withOnClick($modal->getShowSignal());
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
