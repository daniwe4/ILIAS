<?php

namespace CaT\Plugins\TrainingDemandRetarded;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\CourseAction;
use ILIAS\TMS\ActionBuilderUserHelper;
use ILIAS\DI\Container;

/**
 * Keeps basic informations about a course where user is booked as tutor.
 * It is also possible to get more informations about course via CaT\Ente
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 * @author Denis Klöpfer 	<denis.kloepfer@concepts-and-training.de>
 */
class ActionLinksHelper
{
    use ilHandlerObjectHelper;
    use ActionBuilderUserHelper;

    /**
     * @var Container
     */
    protected $dic;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $crs_ref_id;

    /**
     * @var CourseAction | null
     */
    protected $actions = null;


    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->usr_id = (int) $DIC['ilUser']->getId();
    }

    /**
     * Get the dictionary object
     *
     * @return Object
     */
    protected function getDIC()
    {
        return $this->dic;
    }


    public function withRefId(int $crs_ref_id)
    {
        $other = clone $this;
        $other->crs_ref_id = $crs_ref_id;
        return $other;
    }

    /**
     * Returns the ref id of course
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->crs_ref_id;
    }

    /**
     * Returns the ref id of course
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->crs_ref_id;
    }


    protected function getAdmnistratedTrainingActions()
    {
        if ($this->actions === null) {
            $this->actions = $this->getActionsFor(ActionBuilder::CONTEXT_MY_ADMIN_TRAININGS);
        }
        return $this->actions;
    }

    protected function getActionsFor(int $context) : array
    {
        $action_builders = $this->getActionBuilder();
        $actions = [];
        foreach ($action_builders as $action_builder) {
            $actions[] = $action_builder->getCourseActionsFor($context, $this->usr_id);
        }
        $actions = $this->mergeActions($actions);
        ksort($actions);
        return $actions;
    }

    public function getAdministratedTrainingActionLinks()
    {
        if (!$this->getEntityRefId()) {
            throw new \Exception('Undefined entity ref id');
        }
        $search_actions = $this->getAdmnistratedTrainingActions();
        $ret = array();
        foreach ($search_actions as $search_action) {
            $ret[$search_action->getLabel()] = $search_action->getLink($this->ctrl, $this->usr_id);
        }

        return $ret;
    }

    /**
     * Get the UI-factory.
     *
     * @return ILIAS\UI\Factory
     */
    public function getUIFactory()
    {
        global $DIC;
        return $DIC->ui()->factory();
    }
}
