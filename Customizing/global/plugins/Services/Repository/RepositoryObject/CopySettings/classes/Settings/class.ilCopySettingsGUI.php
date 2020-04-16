<?php

declare(strict_types=1);

use CaT\Plugins\CopySettings;
use CaT\Plugins\CopySettings\Settings\Settings;

class ilCopySettingsGUI
{
    use CopySettings\Helper;

    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_EDIT_TITLE = "f_edit_title";
    const F_EDIT_TARGET_GROUPS = "f_edit_target_groups";
    const F_EDIT_TARGET_GROUP_DESC = "f_edit_target_group_description";
    const F_EDIT_CONTENT = "f_edit_content";
    const F_EDIT_BENEFITS = "f_edit_benefits";
    const F_CREATOR_ROLE = "f_creator_role";
    const F_EDIT_VENUE = "f_edit_venue";
    const F_EDIT_PROVIDER = "f_edit_provider";
    const F_ADDITIONAL_INFOS = "f_additional_infos";
    const F_EDIT_IDD_LEARNINGTIME = "f_edit_idd_learningtime";
    const F_NO_MAIL = "f_no_mail";
    const F_SUPPRESS_MAIL_DELIVERY = "f_suppress_mail_delivery";
    const F_EDIT_GTI = "f_edit_gti";
    const F_EDIT_MEMBERLIMITS = "f_edit_memberlimits";

    const ROLE_ADMIN = 0;
    const ROLE_TUTOR = 1;
    const ROLE_MEMBER = 3;

    private static $role_options = array(
        self::ROLE_ADMIN => "crs_admin_role",
        self::ROLE_TUTOR => "crs_tutor_role",
        self::ROLE_MEMBER => "il_crs_member",
    );

    const F_TIME_MODE = "f_time_mode";
    const F_MIN_DAYS_IN_FUTURE = "min_days_in_future";

    const ONLY_FUTURE = "only_future";
    const EVERYTIME = "everytime";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var CopySettings\ilObjectActions
     */
    protected $actions;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(CopySettings\ilObjectActions $actions, \Closure $txt)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->actions = $actions;
        $this->txt = $txt;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveProperties();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function editProperties(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProperties($form);
            return;
        }

        $post = $_POST;
        $object = $this->actions->getObject();
        $object->setTitle($post[self::F_TITLE]);
        $object->setDescription($post[self::F_DESCRIPTION]);

        $edit_title = (bool) $post[self::F_EDIT_TITLE];
        $edit_target_groups = (bool) $post[self::F_EDIT_TARGET_GROUPS];
        $edit_target_group_description = (bool) $post[self::F_EDIT_TARGET_GROUP_DESC];
        $edit_content = (bool) $post[self::F_EDIT_CONTENT];
        $edit_benefits = (bool) $post[self::F_EDIT_BENEFITS];
        $edit_idd_learningtime = (bool) $post[self::F_EDIT_IDD_LEARNINGTIME];
        $role_ids = array_map(
            function ($role_id) {
                return (int) $role_id;
            },
            $post[self::F_CREATOR_ROLE]
        );
        $time_mode = $post[self::F_TIME_MODE];
        $edit_venue = (bool) $post[self::F_EDIT_VENUE];
        $edit_provider = (bool) $post[self::F_EDIT_PROVIDER];
        $additional_infos = (bool) $post[self::F_ADDITIONAL_INFOS];
        $no_mail = (bool) $post[self::F_NO_MAIL];
        $suppress_mail_delivery = (bool) $post[self::F_SUPPRESS_MAIL_DELIVERY];
        $edit_gti = (bool) $post[self::F_EDIT_GTI];
        $edit_memberlimits = (bool) $post[self::F_EDIT_MEMBERLIMITS];
        $min_days_in_future = null;
        if ($time_mode == self::ONLY_FUTURE) {
            $min_days_in_future = $post[self::F_MIN_DAYS_IN_FUTURE];
            if ($min_days_in_future == "") {
                $min_days_in_future = null;
            } else {
                $min_days_in_future = (int) $min_days_in_future;
            }
        }

        $fnc = function (Settings $s) use (
            $edit_title,
            $edit_target_groups,
            $edit_target_group_description,
            $edit_content,
            $edit_benefits,
            $edit_idd_learningtime,
            $role_ids,
            $time_mode,
            $edit_venue,
            $edit_provider,
            $additional_infos,
            $min_days_in_future,
            $no_mail,
            $suppress_mail_delivery,
            $edit_gti,
            $edit_memberlimits
        ) {
            $s = $s
                ->withEditTitle($edit_title)
                ->withEditTargetGroups($edit_target_groups)
                ->withEditTargetGroupDescription($edit_target_group_description)
                ->withEditContent($edit_content)
                ->withEditBenefits($edit_benefits)
                ->withRoleIds($role_ids)
                ->withTimeMode($time_mode)
                ->withMinDaysInFuture($min_days_in_future)
                ->withEditVenue($edit_venue)
                ->withEditProvider($edit_provider)
                ->withAdditionalInfos($additional_infos)
                ->withNoMail($no_mail)
                ->withSuppressMailDelivery($suppress_mail_delivery)
                ->withEditMemberlimits($edit_memberlimits)
            ;

            if ($this->isApplicable("IDD")) {
                $s = $s->withEditIDDLearningTime($edit_idd_learningtime);
            }

            if ($this->isApplicable("GTI")) {
                $s = $s->withEditGti($edit_gti);
            }

            return $s;
        };

        $object->updateExtendedSettings($fnc);
        $object->update();

        ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("property_title"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $ch = new ilCheckboxInputGUI($this->txt("edit_title"), self::F_EDIT_TITLE);
        $ch->setInfo($this->txt("edit_title_info"));
        $form->addItem($ch);

        $ch = new ilCheckboxInputGUI($this->txt("edit_target_groups"), self::F_EDIT_TARGET_GROUPS);
        $ch->setInfo($this->txt("edit_target_groups_info"));
        $form->addItem($ch);

        $ch = new ilCheckboxInputGUI($this->txt("edit_target_group_description"), self::F_EDIT_TARGET_GROUP_DESC);
        $ch->setInfo($this->txt("edit_target_group_description_info"));
        $form->addItem($ch);

        $ch = new ilCheckboxInputGUI($this->txt("edit_content"), self::F_EDIT_CONTENT);
        $ch->setInfo($this->txt("edit_content_info"));
        $form->addItem($ch);

        $ch = new ilCheckboxInputGUI($this->txt("edit_benefits"), self::F_EDIT_BENEFITS);
        $ch->setInfo($this->txt("edit_benefits_info"));
        $form->addItem($ch);

        if ($this->isApplicable("IDD")) {
            $ch =
                new ilCheckboxInputGUI(
                    $this->txt("edit_idd_learningtime"),
                    self::F_EDIT_IDD_LEARNINGTIME
                );
            $ch->setInfo($this->txt("edit_idd_learningtime_info"));
            $form->addItem($ch);
        }

        $ch = new ilCheckboxInputGUI($this->txt("edit_venue"), self::F_EDIT_VENUE);
        $ch->setInfo($this->txt("edit_venue_info"));
        $form->addItem($ch);

        $ch = new ilCheckboxInputGUI($this->txt("edit_provider"), self::F_EDIT_PROVIDER);
        $ch->setInfo($this->txt("edit_provider_info"));
        $form->addItem($ch);

        $ch = new ilCheckboxInputGUI($this->txt("additional_infos"), self::F_ADDITIONAL_INFOS);
        $ch->setInfo($this->txt("additional_infos_info"));
        $form->addItem($ch);

        if ($this->isApplicable("GTI")) {
            $ch = new ilCheckboxInputGUI($this->txt("edit_gti"), self::F_EDIT_GTI);
            $ch->setInfo($this->txt("edit_gti_info"));
            $form->addItem($ch);
        }

        $ch = new ilCheckboxInputGUI($this->txt("edit_memberlimits"), self::F_EDIT_MEMBERLIMITS);
        $ch->setInfo($this->txt("edit_memberlimits_info"));
        $form->addItem($ch);

        $rdg = new ilMultiSelectInputGUI($this->txt("creator_role"), self::F_CREATOR_ROLE);
        $option = self::$role_options;
        array_walk($option, function (&$value) {
            $value = $this->txt($value);
        });
        $rdg->setOptions($option);
        $form->addItem($rdg);

        $ch = new ilCheckboxInputGUI($this->txt("no_mail"), self::F_NO_MAIL);
        $ch->setInfo($this->txt("no_mail_info"));
        $form->addItem($ch);

        $ch = new ilCheckboxInputGUI($this->txt("suppress_mail_delivery"), self::F_SUPPRESS_MAIL_DELIVERY);
        $ch->setInfo($this->txt("suppress_mail_delivery_info"));
        $form->addItem($ch);

        $rdg = new ilRadioGroupInputGUI($this->txt("time_mode"), self::F_TIME_MODE);
        $rdg->setRequired(true);
        $option = new ilRadioOption($this->txt(self::ONLY_FUTURE), self::ONLY_FUTURE);
        $ti = new ilNumberInputGUI($this->txt(self::F_MIN_DAYS_IN_FUTURE), self::F_MIN_DAYS_IN_FUTURE);
        $ti->setRequired(true);
        $ti->setMinValue(0);
        $option->addSubItem($ti);
        $rdg->addOption($option);
        $option = new ilRadioOption($this->txt(self::EVERYTIME), self::EVERYTIME);
        $rdg->addOption($option);
        $form->addItem($rdg);

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $object = $this->actions->getObject();

        $values = array();
        $values[self::F_TITLE] = $object->getTitle();
        $values[self::F_DESCRIPTION] = $object->getDescription();

        $settings = $object->getExtendedSettings();
        $values[self::F_EDIT_TITLE] = $settings->getEditTitle();
        $values[self::F_EDIT_TARGET_GROUPS] = $settings->getEditTargetGroups();
        $values[self::F_EDIT_TARGET_GROUP_DESC] = $settings->getEditTargetGroupDescription();
        $values[self::F_EDIT_CONTENT] = $settings->getEditContent();
        $values[self::F_EDIT_BENEFITS] = $settings->getEditBenefits();
        $values[self::F_EDIT_IDD_LEARNINGTIME] = $settings->getEditIDDLearningTime();
        $values[self::F_CREATOR_ROLE] = $settings->getRoleIds();
        $values[self::F_EDIT_VENUE] = $settings->getEditVenue();
        $values[self::F_EDIT_PROVIDER] = $settings->getEditProvider();
        $values[self::F_ADDITIONAL_INFOS] = $settings->getAdditionalInfos();
        $values[self::F_NO_MAIL] = $settings->getNoMail();
        $values[self::F_SUPPRESS_MAIL_DELIVERY] = $settings->getSuppressMailDelivery();
        $values[self::F_EDIT_GTI] = $settings->isEditGti();
        $values[self::F_EDIT_MEMBERLIMITS] = $settings->getEditMemberlimits();

        $time_mode = $settings->getTimeMode();
        if (is_null($time_mode) || $time_mode == "") {
            $time_mode = self::EVERYTIME;
        }
        $values[self::F_TIME_MODE] = $time_mode;
        $values[self::F_MIN_DAYS_IN_FUTURE] = $settings->getMinDaysInFuture();

        $form->setValuesByArray($values);
    }

    protected function isApplicable(string $purpose_name)
    {
        if (!\ilPluginAdmin::isPluginActive('xetr')) {
            return false;
        }

        $pl = \ilPluginAdmin::getPluginObjectById('xetr');
        $actions = $pl->getConfigActionsFor($purpose_name);
        $settings = $actions->select();
        if (is_null($settings) || !$settings->getAvailable()) {
            return false;
        }

        return true;
    }
}
