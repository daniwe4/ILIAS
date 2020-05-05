<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types = 1);

use CaT\Plugins\CourseMember\SignatureList\ConfigurableList as CLS;

class ilConfigurableConfigGUI
{
    const GET_TEMPLATE_ID = "template_id";

    const FIELD_NAME = "field_name";
    const FIELD_DESCRIPTION = "field_description";
    const FIELD_ID = "field_id";
    const FIELD_PRESETS = "presets";
    const FIELD_BLANK = "blank";
    const FIELD_SELECTED_USER = "selection_of_users";
    const FIELD_DEFAULT = "default";
    const FIELD_MAIL_TEMPLATE = 'mail_template_id';

    const CMD_SHOW = "cmd_show";
    const CMD_SAVE = "cmd_save";
    const CMD_REQUEST_CREATE = "cmd_request_create";
    const CMD_CREATE = "cmd_create";
    const CMD_BACK = "back";

    protected $tpl;
    protected $ctrl;
    protected $lng;
    protected $af;
    protected $repo;
    protected $id;
    protected $parent;

    public function __construct(
        \ilGlobalTemplateInterface $tpl,
        \ilCtrl $ctrl,
        \ilLanguage $lng,
        \ilPlugin $plugin,
        CLS\AvailableFields $af,
        CLS\ConfigurableListConfigRepo $repo
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->plugin = $plugin;
        $this->af = $af;
        $this->repo = $repo;

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('mem');
    }


    public function withParentGUI(
        ilConfigurableOverviewGUI $parent
    ) : ilConfigurableConfigGUI {
        $other = clone $this;
        $other->parent = $parent;
        return $other;
    }

    protected function getID()
    {
        if (!$this->id) {
            $this->id = (int) $_GET[self::GET_TEMPLATE_ID];
        }
        return $this->id;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW:
                $this->show();
                break;
            case self::CMD_REQUEST_CREATE:
                $this->requestCreate();
                break;
            case self::CMD_CREATE:
                $this->create();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            case self::CMD_BACK:
                $this->back();
                break;
            default:
                throw new Exception('unknown command ' . $cmd);
        }
    }

    protected function show()
    {
        $form = $this->initUpdateForm();
        $this->tpl->setContent($this->fillFormById($form, $this->getID())->getHTML());
    }

    protected function requestCreate()
    {
        $form = $this->initCreationForm();
        $this->tpl->setContent($this->fillFormByDefault($form)->getHTML());
    }

    protected function create()
    {
        $form = $this->initCreationForm();
        $form->setValuesByPost();
        $correct = true;

        if (!$form->checkInput()) {
            $correct = false;
        }

        $name = $form->getItemByPostVar(self::FIELD_NAME)->getValue();
        if ($this->repo->exists($name)) {
            \ilUtil::sendFailure($this->txt('redundand_name_failure'));
            $correct = false;
        }

        if (!$correct) {
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $this->createByForm($form)->getId();
        \ilUtil::sendSuccess($this->txt('siglist_template_created'), true);
        $this->ctrl->redirectByClass(
            "ilConfigurableOverviewGUI",
            ilConfigurableOverviewGUI::CMD_SHOW
        );
    }

    protected function save()
    {
        $form = $this->initUpdateForm();
        $form->setValuesByPost();
        if (!$this->saveByForm($form)) {
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $this->ctrl->redirectByClass(
            "ilConfigurableOverviewGUI",
            ilConfigurableOverviewGUI::CMD_SHOW
        );
    }

    protected function saveByForm(ilPropertyFormGUI $form) : bool
    {
        if (!$form->checkInput()) {
            return false;
        }
        $cfg = $this->getConfigFromFilledForm($form);
        $aux_id = $this->repo->idByName($cfg->getName());
        if (
            $aux_id !== $cfg->getId() &&
            $aux_id !== CLS\ConfigurableListConfigRepo::NONE_INT
        ) {
            \ilUtil::sendFailure($this->txt('redundand_name_failure'), true);
            return false;
        }
        \ilUtil::sendSuccess($this->txt('siglist_changes_saved'), true);
        $this->repo->save($cfg);
        return true;
    }

    protected function fillFormById(ilPropertyFormGUI $form, int $id) : ilPropertyFormGUI
    {
        $cfg = $this->repo->load($id);
        $preset = array_merge(
            $cfg->getStandardFields(),
            $cfg->getLpFields(),
            $cfg->getUdfFields()
        );
        $roles = $cfg->getRoleFields();

        $form->getItemByPostVar(self::FIELD_ID)->setValue($cfg->getId());
        $form->getItemByPostVar(self::FIELD_NAME)->setValue($cfg->getName());
        $form->getItemByPostVar(self::FIELD_DESCRIPTION)->setValue($cfg->getDescription());
        $form->getItemByPostVar(self::FIELD_PRESETS)->setValue($preset);
        $form->getItemByPostVar(self::FIELD_SELECTED_USER)->setValue($roles);
        $form->getItemByPostVar(self::FIELD_BLANK)->setValue($cfg->getAdditionalFields());
        $form->getItemByPostVar(self::FIELD_DEFAULT)->setValue((int) $cfg->isDefault());
        $form->getItemByPostVar(self::FIELD_MAIL_TEMPLATE)->setValue($cfg->getMailTemplateId());

        return $form;
    }

    protected function fillFormByDefault(ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $settings = new ilUserFormSettings('crss_pview', -1);
        $settings->exportToForm($form);
        return $form;
    }

    protected function getConfigFromFilledForm(ilPropertyFormGUI $form) : CLS\ConfigurableListConfig
    {
        $id = (int) $form->getItemByPostVar(self::FIELD_ID)->getValue();
        $name = $form->getItemByPostVar(self::FIELD_NAME)->getValue();
        $description = $form->getItemByPostVar(self::FIELD_DESCRIPTION)->getValue();
        $default = (bool) $form->getItemByPostVar(self::FIELD_DEFAULT)->getValue();
        $mail_template_id = $form->getItemByPostVar(self::FIELD_MAIL_TEMPLATE)->getValue();

        list($standard_fields, $lp_fields, $udf_fields) = $this->getPresetsFromForm($form);
        $additional_fields = $this->getAdditionalFieldsFromPost($_POST);
        $role_fields = $this->getRolesFromForm($form);

        return new CLS\ConfigurableListConfig(
            $id,
            $name,
            $description,
            $standard_fields,
            $lp_fields,
            $udf_fields,
            $role_fields,
            $additional_fields,
            $default,
            strtoupper($mail_template_id)
        );
    }

    protected function createByForm(ilPropertyFormGUI $form) : CLS\ConfigurableListConfig
    {
        $name = $form->getItemByPostVar(self::FIELD_NAME)->getValue();
        $description = $form->getItemByPostVar(self::FIELD_DESCRIPTION)->getValue();
        $mail_template_id = $form->getItemByPostVar(self::FIELD_MAIL_TEMPLATE)->getValue();

        list($standard_fields, $lp_fields, $udf_fields) = $this->getPresetsFromForm($form);
        $additional_fields = $this->getAdditionalFieldsFromPost($_POST);
        $role_fields = $this->getRolesFromForm($form);

        return $this->repo->create(
            $name,
            $description,
            $standard_fields,
            $lp_fields,
            $udf_fields,
            $role_fields,
            $additional_fields,
            strtoupper($mail_template_id)
        );
    }

    protected function initCreationForm() : ilPropertyFormGUI
    {
        $form = $this->initForm();
        $form->addCommandButton(self::CMD_CREATE, $this->lng->txt('save'));
        $form->addCommandButton(self::CMD_BACK, $this->lng->txt('cancel'));
        return $form;
    }

    protected function initUpdateForm() : ilPropertyFormGUI
    {
        $form = $this->initForm();

        $id = new ilHiddenInputGUI(self::FIELD_ID);
        $form->addItem($id);

        $form->addCommandButton(self::CMD_SAVE, $this->lng->txt('save'));
        $form->addCommandButton(self::CMD_BACK, $this->lng->txt('cancel'));
        return $form;
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt('template_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $name = new ilTextInputGUI($this->txt('template_name'), self::FIELD_NAME);
        $name->setRequired(true);
        $form->addItem($name);

        $desc = new ilTextInputGUI($this->txt('template_description'), self::FIELD_DESCRIPTION);
        $form->addItem($desc);

        $desc = new ilTextInputGUI($this->txt('template_mail_id'), self::FIELD_MAIL_TEMPLATE);
        $desc->setInfo($this->txt('template_mail_id_info'));
        $form->addItem($desc);

        $default = new ilHiddenInputGUI(self::FIELD_DEFAULT);
        $form->addItem($default);

        $ufields = new ilCheckboxGroupInputGUI($this->lng->txt('user_detail'), self::FIELD_PRESETS);
        foreach (array_merge(
            $this->af->getStandardFields(),
            $this->af->getUdfFields(),
            $this->af->getLpFields()
        ) as $id => $name) {
            $ufields->addOption(new ilCheckboxOption($name, $id));
        }
        $form->addItem($ufields);

        $additional = new ilTextInputGUI($this->lng->txt('event_blank_columns'), self::FIELD_BLANK);
        $additional->setMulti(true);
        $form->addItem($additional);

        $roles = new ilCheckboxGroupInputGUI($this->lng->txt('event_user_selection'), self::FIELD_SELECTED_USER);
        foreach (array_merge(
            $this->af->getRoles()
        ) as $id => $name) {
            $roles->addOption(new ilCheckboxOption($name, $id));
        }
        $form->addItem($roles);

        return $form;
    }

    protected function back()
    {
        $this->ctrl->redirectByClass(
            'ilConfigurableOverviewGUI',
            ilConfigurableOverviewGUI::CMD_SHOW
        );
    }

    protected function getPresetsFromForm(ilPropertyFormGUI $form) : array
    {
        $standard_fields = [];
        $lp_fields = [];
        $udf_fields = [];
        $input = $form->getItemByPostVar(self::FIELD_PRESETS)->getValue();
        if (is_array($input)) {
            foreach (array_keys($this->af->getStandardFields()) as $field) {
                if (in_array($field, $input)) {
                    $standard_fields[] = $field;
                }
            }

            $lp_fields = [];
            foreach (array_keys($this->af->getLpFields()) as $field) {
                if (in_array($field, $input)) {
                    $lp_fields[] = $field;
                }
            }
            $udf_fields = [];
            foreach (array_keys($this->af->getUdfFields()) as $field) {
                if (in_array($field, $input)) {
                    $udf_fields[] = $field;
                }
            }
        }

        return [
            $standard_fields,
            $lp_fields,
            $udf_fields
        ];
    }

    protected function getAdditionalFieldsFromPost(array $post) : array
    {
        $additional_fields = $post[self::FIELD_BLANK];

        return array_filter(
            array_map(
                    function ($val) {
                        return trim((string) $val);
                    },
                    $additional_fields
                ),
            function ($field_title) {
                return $field_title !== '';
            }
            );
    }

    protected function getRolesFromForm(ilPropertyFormGUI $form) : array
    {
        $input = $form->getItemByPostVar(self::FIELD_SELECTED_USER)->getValue();
        $role_fields = [];
        if (is_array($input)) {
            foreach (array_keys($this->af->getRoles()) as $field) {
                if (in_array($field, $input)) {
                    $role_fields[] = $field;
                }
            }
        }

        return $role_fields;
    }

    protected function txt(string $code) : string
    {
        return $this->plugin->txt($code);
    }
}
