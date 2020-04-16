<?php

declare(strict_types=1);

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use CaT\Plugins\TrainingProvider\Trainer;
use CaT\Plugins\TrainingProvider\ilActions;

/**
 * GUI class for Trainer configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilTrainerGUI
{
    const CMD_SHOW_TRAINER = "showTrainer";
    const CMD_NEW_TRAINER = "newTrainer";
    const CMD_ADD_TRAINER = "addTrainer";
    const CMD_EDIT_TRAINER = "editTrainer";
    const CMD_UPDATE_TRAINER = "updateTrainer";
    const CMD_DEL_TRAINER = "deleteTrainer";
    const CMD_DEL_CONFIRM_TRAINER = "deleteConfirmTrainer";
    const CMD_APPLY_FILTER = "applyFilter";
    const CMD_RESET_FILTER = "resetFilter";

    const F_PROVIDER_FILTER = "providerFilter";
    const F_ACTIVE_FILTER = "activeFilter";

    /**
     * @var $ilCtrl
     */
    protected $gCtrl;

    /**
     * @var ilTemplate
     */
    protected $gTpl;

    /**
     * @var ilToolbarGUI
     */
    protected $gToolbar;

    public function __construct($parent_object, $plugin_object, $actions)
    {
        global $ilCtrl, $tpl, $ilToolbar;
        $this->gCtrl = $ilCtrl;
        $this->gTpl = $tpl;
        $this->gToolbar = $ilToolbar;

        $this->parent_object = $parent_object;
        $this->plugin_object = $plugin_object;
        $this->actions = $actions;

        $this->determineFilterValues();
    }

    public function executeCommand()
    {
        $cmd = $this->gCtrl->getCmd(self::CMD_SHOW_TRAINER);

        switch ($cmd) {
            case self::CMD_SHOW_TRAINER:
            case self::CMD_NEW_TRAINER:
            case self::CMD_ADD_TRAINER:
            case self::CMD_EDIT_TRAINER:
            case self::CMD_UPDATE_TRAINER:
            case self::CMD_DEL_TRAINER:
            case self::CMD_DEL_CONFIRM_TRAINER:
            case self::CMD_APPLY_FILTER:
            case self::CMD_RESET_FILTER:
                $this->$cmd();
                break;
        }
    }

    protected function showTrainer()
    {
        $this->setToolbar();
        $table = new Trainer\ilTrainerTableGUI($this, $this->plugin_object, self::CMD_SHOW_TRAINER);
        $table->setFilterValues(array(self::F_PROVIDER_FILTER => $this->provider_filter_value, self::F_ACTIVE_FILTER => $this->active_filter_value));
        $this->fillFilterItem($table);
        $data = $this->plugin_object->getActions()->getTrainersRaw($this->provider_filter_value, $this->active_filter_value);
        $data = $this->modifyName($data);
        $table->setData($data);

        $this->gCtrl->setParameter($this, "cmd", $this->provider_filter_value);
        $this->gTpl->setContent($table->getHtml());
    }

    protected function modifyName(array $data) : array
    {
        foreach ($data as $key => &$dat) {
            $dat["name"] = $dat["lastname"] . ", " . $dat["title"] . " " . $dat["firstname"];
        }
        return $data;
    }

    protected function newTrainer($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
        }

        $form->setTitle($this->txt("new_trainer_title"));
        $form->setFormAction($this->gCtrl->getFormAction($this));

        $form->addCommandButton(self::CMD_ADD_TRAINER, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_TRAINER, $this->txt("cancel"));

        $this->gTpl->setContent($form->getHtml());
    }

    protected function addTrainer()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->newTrainer($form);
            return;
        }

        if (!$this->checkTrainerValues($form, $_POST)) {
            $form->setValuesByPost();
            $this->newTrainer($form);
            return;
        }

        $post = $_POST;
        if (isset($post[ilActions::F_TRAINER_FEE]) || trim($post[ilActions::F_TRAINER_FEE]) != "") {
            $fee = floatval(trim($post[ilActions::F_TRAINER_FEE]));
        } else {
            $fee = 0.0;
        }

        $title = trim($post[ilActions::F_TRAINER_TITLE]);
        $salutation = trim($post[ilActions::F_TRAINER_SALUTATION]);

        $firstname = trim($post[ilActions::F_TRAINER_FIRSTNAME]);
        $lastname = trim($post[ilActions::F_TRAINER_LASTNAME]);
        $email = trim($post[ilActions::F_TRAINER_EMAIL]);
        $phone = trim($post[ilActions::F_TRAINER_PHONE]);
        $mobile_number = trim($post[ilActions::F_TRAINER_MOBILE_NUMBER]);
        $active = (bool) trim($post[ilActions::F_TRAINER_ACTIVE]);
        $provider = (int) trim($post[ilActions::F_TRAINER_PROVIDER]);
        $extra_infos = trim($post[ilActions::F_TRAINER_EXTRA_INFOS]);

        $this->actions->createTrainer($title, $salutation, $firstname, $lastname, $provider, $email, $phone, $mobile_number, $fee, $extra_infos, $active);
        \ilUtil::sendSuccess($this->txt('trainer_added'), true);
        $this->gCtrl->redirect($this);
    }

    protected function editTrainer()
    {
        if (!isset($_GET["id"])) {
            ilUtil::sendFailure($this->plugin_object->txt("no_trainer_id"), true);
            $this->gCtrl->redirect($this);
            return;
        }
        $trainer_id = (int) $_GET["id"];

        $this->editTrainerForm($trainer_id);
    }

    protected function editTrainerForm($trainer_id, $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $values = $this->actions->getTrainerValues($trainer_id);
            $form->setValuesByArray($values);
        }

        $form->setTitle($this->txt("edit_trainer_title"));
        $this->gCtrl->setParameter($this, "id", $trainer_id);
        $form->setFormAction($this->gCtrl->getFormAction($this));
        $this->gCtrl->setParameter($this, "id", null);

        $form->addCommandButton(self::CMD_UPDATE_TRAINER, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_TRAINER, $this->txt("cancel"));

        $this->gTpl->setContent($form->getHtml());
    }

    protected function updateTrainer()
    {
        $form = $this->initForm();
        $id = (int) $_GET["id"];

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editTrainerForm($id, $form);
            return;
        }

        if (!$this->checkTrainerValues($form, $_POST)) {
            $form->setValuesByPost();
            $this->editTrainerForm($id, $form);
            return;
        }

        $this->actions->updateTrainer($id, $_POST);
        \ilUtil::sendSuccess($this->txt('trainer_updated'), true);
        $this->gCtrl->redirect($this);
    }

    protected function deleteConfirmTrainer()
    {
        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI();

        $confirmation->setFormAction($this->gCtrl->getFormAction($this, self::CMD_DEL_TRAINER));
        $confirmation->setHeaderText($this->txt("confirm_delete_trainer"));
        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_TRAINER);
        $confirmation->setConfirm($this->txt("delete_trainer"), self::CMD_DEL_TRAINER);

        $confirmation->addHiddenItem("id", (int) $_GET["id"]);
        $this->gTpl->setContent($confirmation->getHTML());
    }

    protected function deleteTrainer()
    {
        if (!isset($_POST["id"])) {
            ilUtil::sendFailure($this->plugin_object->txt("no_trainer_id"), true);
            $this->gCtrl->redirect($this);
            return;
        }

        $id = (int) $_POST["id"];
        $this->actions->removeTrainer($id);
        $this->gCtrl->redirect($this);
    }

    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();

        $provider_options = $this->actions->getProviderOptions();
        $provider_filter_value = (int) $_GET["provider_id"];

        $this->getAddNewFormItems($form, $provider_options, $provider_filter_value);

        return $form;
    }

    protected function setToolbar()
    {
        $this->gCtrl->setParameterByClass("ilTrainerGUI", "provider_id", $this->provider_filter_value);
        $this->gToolbar->addButton($this->txt("new_trainer"), $this->gCtrl->getLinkTargetByClass(array("ilTrainingProviderConfigGUI", "ilTrainerGUI"), self::CMD_NEW_TRAINER));
        $this->gCtrl->setParameterByClass("ilTrainerGUI", "provider_id", null);
    }

    protected function txt($code)
    {
        return $this->plugin_object->txt($code);
    }

    protected function applyFilter()
    {
        $post = $_POST;
        $this->provider_filter_value = $this->getProviderFilterValue($post);
        $this->active_filter_value = $this->getActiveFilterValue($post);
        $this->showTrainer();
    }

    protected function resetFilter()
    {
        $this->provider_filter_value = null;
        $this->active_filter_value = array();
        $this->showTrainer();
    }

    public function getAddNewFormItems($form, array $provider_options, $provider_filter_value)
    {
        $ti = new \ilTextInputGUI($this->txt("trainer_title"), ilActions::F_TRAINER_TITLE);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("salutation"), ilActions::F_TRAINER_SALUTATION);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("firstname"), ilActions::F_TRAINER_FIRSTNAME);
        $ti->setInfo(sprintf($this->txt("firstname_info"), ilActions::TRAINER_FIRSTNAME_LENGTH));
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("lastname"), ilActions::F_TRAINER_LASTNAME);
        $ti->setInfo(sprintf($this->txt("lastname_info"), ilActions::TRAINER_FIRSTNAME_LENGTH));
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("email"), ilActions::F_TRAINER_EMAIL);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("phone"), ilActions::F_TRAINER_PHONE);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("mobile_number"), ilActions::F_TRAINER_MOBILE_NUMBER);
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("fee"), ilActions::F_TRAINER_FEE);
        $ti->allowDecimals(true);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("extra_infos"), ilActions::F_TRAINER_EXTRA_INFOS);
        $ti->setMaxLength(256);
        $form->addItem($ti);

        $cb = new \ilCheckboxInputGUI($this->txt("active"), ilActions::F_TRAINER_ACTIVE);
        $form->addItem($cb);

        $si = new \ilSelectInputGUI($this->txt("provider"), ilActions::F_TRAINER_PROVIDER);
        $options = array(null => $this->txt("please_select")) + $provider_options;
        $si->setOptions($options);
        $si->setValue($provider_filter_value);
        $si->setRequired(true);
        $form->addItem($si);
    }

    /**
     * Checks values are legal
     *
     * @param \ilPropertyFormGUI 		$form
     * @param string[] 					$post
     *
     * @return bool
     */
    public function checkTrainerValues($form, &$post)
    {
        $ret = true;

        $firstname = trim($post[ilActions::F_TRAINER_FIRSTNAME]);
        if (strlen($firstname) < ilActions::TRAINER_FIRSTNAME_LENGTH) {
            $gui = $form->getItemByPostVar(ilActions::F_TRAINER_FIRSTNAME);
            $gui->setAlert($this->txt("trainer_firstname_short"));
            $ret = false;
        }

        $lastname = trim($post[ilActions::F_TRAINER_LASTNAME]);
        if (strlen($lastname) < ilActions::TRAINER_LASTNAME_LENGTH) {
            $gui = $form->getItemByPostVar(ilActions::F_TRAINER_LASTNAME);
            $gui->setAlert($this->txt("trainer_lastname_short"));
            $ret = false;
        }

        $email = trim($post[ilActions::F_EMAIL]);
        if ($email != "" && !preg_match(ilActions::MAIL_REGEXP, $email)) {
            $gui = $form->getItemByPostVar(ilActions::F_EMAIL);
            $gui->setAlert($this->txt("email_not_valid"));
            $ret = false;
        }

        return $ret;
    }

    public function fillFilterItem(Trainer\ilTrainerTableGUI $table)
    {
        require_once("Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new \ilSelectInputGUI($this->txt("provider"), self::F_PROVIDER_FILTER);
        $option = array(null => $this->txt("please_select")) + $this->plugin_object->getActions()->getProviderOptions();
        $si->setOptions($option);
        $si->setValue($this->provider_filter_value);
        $table->addFilterItem($si);

        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        $si = new \ilMultiSelectInputGUI($this->txt("trainer"), self::F_ACTIVE_FILTER);
        $option = array(1 => $this->txt("active"), 0 => $this->txt("inactive"));
        $si->setOptions($option);
        $si->setValue($this->active_filter_value);
        $table->addFilterItem($si);
    }

    /**
     * Get the value of the provider filter
     *
     * @param array $post
     *
     * @return int|null
     */
    protected function getProviderFilterValue(array $post)
    {
        if (isset($post[self::F_PROVIDER_FILTER]) && $post[self::F_PROVIDER_FILTER] != "") {
            return $post[self::F_PROVIDER_FILTER];
        }

        return null;
    }

    /**
     * Get the value of the active filter
     *
     * @param array $post
     *
     * @return int|null
     */
    protected function getActiveFilterValue(array $post)
    {
        if (isset($post[self::F_ACTIVE_FILTER]) && $post[self::F_ACTIVE_FILTER] != "") {
            return $post[self::F_ACTIVE_FILTER];
        }

        return array();
    }

    /**
     * Determin filter values from get
     *
     * @return null
     */
    protected function determineFilterValues()
    {
        $this->provider_filter_value = null;
        $this->active_filter_value = array();

        $filter_values = $_GET["filter_values"];

        if ($filter_values) {
            $filter_values = unserialize(base64_decode($filter_values));
            $this->provider_filter_value = $filter_values[self::F_PROVIDER_FILTER];
            $this->active_filter_value = $filter_values[self::F_ACTIVE_FILTER];
        }
    }
}
