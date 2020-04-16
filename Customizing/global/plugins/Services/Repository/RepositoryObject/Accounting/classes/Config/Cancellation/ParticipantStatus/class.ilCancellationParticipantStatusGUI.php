<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\Config\Cancellation\ParticipantStatus;

class ilCancellationParticipantStatusGUI
{
    const CMD_SHOW_STATES = "showStates";
    const CMD_SAVE_STATES = "saveStates";

    const F_STATES = "states";

    protected $ctrl;
    protected $tpl;
    protected $rbacreview;
    protected $txt;
    protected $states_db;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilRbacReview $rbacreview,
        Closure $txt,
        ParticipantStatus\DB $states_db
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->rbacreview = $rbacreview;
        $this->txt = $txt;
        $this->states_db = $states_db;
    }

    /**
     * @inheritDoc
     * @throws Exception if cmd is not known
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW_STATES:
                $this->showStates();
                break;
            case self::CMD_SAVE_STATES:
                $this->saveStates();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showStates(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveStates()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showStates($form);
            return;
        }

        $this->states_db->saveStates($_POST[self::F_STATES]);

        ilUtil::sendSuccess($this->txt("cancellation_states_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_STATES);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("cancellation_states"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_STATES, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_STATES, $this->txt("cancel"));

        $mi = new ilMultiSelectInputGUI($this->txt("states"), self::F_STATES);
        $mi->setWidthUnit("%");
        $mi->setWidth(100);
        $mi->setHeight(200);
        $mi->setOptions($this->getParticipantStatusOptions());
        $form->addItem($mi);

        return $form;
    }

    /**
     * @return string[]
     */
    protected function getParticipantStatusOptions() : array
    {
        if (!ilPluginAdmin::isPluginActive("xcmb")) {
            return [];
        }

        /**
         * @var ilCourseMemberPlugin $pl
         */
        $pl = ilPluginAdmin::getPluginObjectById("xcmb");

        /**
         * @var CaT\Plugins\CourseMember\LPOptions\ilActions $actions
         */
        $actions = $pl->getLPOptionActions();
        $ret = [];
        foreach ($actions->getLPOptions() as $lp_option) {
            if ($lp_option->getActive()) {
                $ret[$lp_option->getId() . "_" . $lp_option->getILIASLP()] = $lp_option->getTitle();
            }
        }

        return $ret;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = [
            self::F_STATES => $this->states_db->getStates()
        ];

        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
