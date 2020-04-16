<?php declare(strict_types=1);

use CaT\Plugins\OrguByMailDomain as OMD;

class DomainConfigurationGUI
{
    public function __construct(
        OMD\Configuration\Repository $config_repository,
        OMD\Orgus $orgus,
        \ilOrguByMailDomainPlugin $plugin,
        \ilCtrl $ctrl,
        \ilTemplate $tpl
    ) {
        $this->config_repository = $config_repository;
        $this->orgus = $orgus;
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
    }

    const CMD_SAVE = 'save';
    const CMD_EDIT = 'edit';
    const CMD_SAVE_NEW = 'save_new';
    const CMD_CREATE_REQUEST = 'create_request';
    const CMD_BACK = 'back';


    const POST_DOMAIN = 'domain';
    const POST_DOMAIN_ID = 'domain_id';
    const POST_DOMAIN_DESCRIPTION = 'domain_desc';
    const POST_ORGUS = 'orgus';
    const POST_POSITION = 'position';

    const GET_DOMAIN_ID = 'domain_id';

    public function executeCommand()
    {
        $this->cmd = $this->ctrl->getCmd(self::CMD_EDIT);
        switch ($this->cmd) {
            case self::CMD_CREATE_REQUEST:
                $this->createRequest();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            case self::CMD_EDIT:
                $domain_id = (int) $_GET[self::GET_DOMAIN_ID];
                $this->edit($domain_id);
                break;
            case self::CMD_SAVE_NEW:
                $this->saveNew();
                break;
            case self::CMD_BACK:
                $this->back();
        }
        return true;
    }

    protected function back()
    {
        $this->ctrl->redirectByClass(
            ['ilObjComponentSettingsGUI'
            ,'ilOrguByMailDomainConfigGUI'
            ,'DomainConfigurationOverviewGUI'],
            DomainConfigurationOverviewGUI::CMD_OVERVIEW
        );
    }


    protected function createRequest()
    {
        $form = $this->form($this->plugin->txt('create'));

        $form->addCommandButton(self::CMD_BACK, $this->plugin->txt('abort'));
        $form->addCommandButton(self::CMD_SAVE_NEW, $this->plugin->txt('create'));

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }


    protected function edit(int $domain_id)
    {
        $form = $this->setFormValuesByConfig(
            $this->editForm(),
            $this->config_repository->loadById($domain_id)
        );
        $this->tpl->setContent($form->getHTML());
    }

    protected function setFormValuesByConfig(
        \ilPropertyFormGUI $form,
        OMD\Configuration\Configuration $config
    ) {
        $form->setValuesByArray(
            [self::POST_DOMAIN_ID => $config->getId()
            ,self::POST_DOMAIN => $config->getTitle()
            ,self::POST_ORGUS => $config->getOrguIds()
            ,self::POST_POSITION => $config->getPosition()
            ,self::POST_DOMAIN_DESCRIPTION => $config->getDescription()]
        );
        return $form;
    }

    protected function clearOrgus(array $orgus)
    {
        $filtered = [];
        foreach ($orgus as $orgu) {
            $orgu = (int) $orgu;
            if ($orgu > 0) {
                $filtered[] = $orgu;
            }
        }
        $proper_orgus = array_keys($this->orgus->orguList());
        return array_unique(array_intersect($proper_orgus, $filtered));
    }

    protected function saveNew()
    {
        $form = $this->form();
        $form->setValuesByPost();
        $check_input = $form->checkInput();
        $title = htmlspecialchars($form->getItemByPostVar(self::POST_DOMAIN)->getValue());
        $orgus = $this->clearOrgus($form->getInput(self::POST_ORGUS));
        $position = (int) $form->getItemByPostVar(self::POST_POSITION)->getValue();

        if (!$check_input) {
            $this->createRequest();
        } elseif ($this->config_repository->loadByTitle($title) !== null) {
            \ilUtil::sendFailure($this->plugin->txt('domain_failure'));
            $this->createRequest();
        } elseif (count($orgus) === 0) {
            \ilUtil::sendFailure($this->plugin->txt('no_orgus_failure'));
            $this->createRequest();
        } elseif (!in_array($position, array_keys($this->orgus->positionList()))) {
            \ilUtil::sendFailure($this->plugin->txt('no_position_failure'));
            $this->createRequest();
        } else {
            $desc = htmlspecialchars((string) $form->getItemByPostVar(self::POST_DOMAIN_DESCRIPTION)->getValue());

            $domain = $this->config_repository->create(
                $title,
                $orgus,
                $position,
                $desc
            );
            \ilUtil::sendSuccess($this->plugin->txt('new_domain_created'), true);
            $this->back();
        }
    }

    protected function save()
    {
        $form = $this->editForm();

        $form->setValuesByPost();
        $check_input = $form->checkInput();

        $title = htmlspecialchars($form->getItemByPostVar(self::POST_DOMAIN)->getValue());
        $domain_id = (int) $form->getItemByPostVar(self::POST_DOMAIN_ID)->getValue();
        $orgus = $this->clearOrgus($form->getInput(self::POST_ORGUS));
        $position = (int) $form->getItemByPostVar(self::POST_POSITION)->getValue();

        $current_domain_with_title = $this->config_repository->loadByTitle($title);
        $invalid_name = $current_domain_with_title !== null
                    && $current_domain_with_title->getId() !== $domain_id;


        if ($check_input) {
            if ($invalid_name) {
                \ilUtil::sendFailure($this->plugin->txt('role_exists_failure'));
            } elseif (count($orgus) === 0) {
                \ilUtil::sendFailure($this->plugin->txt('orgus_failure'));
            } elseif (!in_array($position, array_keys($this->orgus->positionList()))) {
                \ilUtil::sendFailure($this->plugin->txt('no_position_failure'));
            } else {
                $current_domain_stored = $this->config_repository->loadById($domain_id);
                $desc = htmlspecialchars($form->getItemByPostVar(self::POST_DOMAIN_DESCRIPTION)->getValue());
                $this->config_repository->update(
                    $current_domain_stored
                        ->withTitle($title)
                        ->withOrguIds($orgus)
                        ->withPosition($position)
                        ->withDescription($desc)
                );
                \ilUtil::sendSuccess($this->plugin->txt('domain_updated'));
                $this->edit($domain_id);
            }
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function editForm()
    {
        $form = $this->form($this->plugin->txt('edit_domain'));

        $form->addCommandButton(self::CMD_BACK, $this->plugin->txt('back'));
        $form->addCommandButton(self::CMD_SAVE, $this->plugin->txt('save'));

        return $form;
    }

    protected function form(string $title = "")
    {
        $form = $form = new ilPropertyFormGUI();
        $form->setTitle($title);

        $form->setFormAction($this->ctrl->getFormAction($this));
        $title = new ilTextInputGUI(
            $this->plugin->txt('domain_title'),
            self::POST_DOMAIN
        );
        $title->setRequired(true);
        $title->setMaxLength(128);
        $form->addItem($title);

        $desc = new ilTextAreaInputGUI(
            $this->plugin->txt('domain_description'),
            self::POST_DOMAIN_DESCRIPTION
        );
        $form->addItem($desc);

        $orgus = new ilSelectInputGUI(
            $this->plugin->txt('organisational_unit'),
            self::POST_ORGUS
        );
        $orgus->setMulti(true);
        $orgus->setRequired(true);
        $orgu_options = [ -1 => '--'];
        $orgu_list = $this->orgus->orguList();
        uasort(
            $orgu_list,
            function ($one, $other) {
                return strcasecmp($one, $other);
            }
        );
        foreach ($orgu_list as $ref_id => $title) {
            $orgu_options[$ref_id] = $title;
        }
        $orgus->setOptions($orgu_options);
        $form->addItem($orgus);

        $position = new ilSelectInputGUI(
            $this->plugin->txt('position'),
            self::POST_POSITION
        );
        $position->setRequired(true);
        $positions = [ -1 => '--'];
        $position_list = $this->orgus->positionList();
        uasort(
            $position_list,
            function ($one, $other) {
                return strcasecmp($one, $other);
            }
        );
        foreach ($position_list as $position_id => $position_title) {
            $positions[$position_id] = $position_title;
        }
        $position->setOptions($positions);
        $form->addItem($position);

        $domain_id = new ilHiddenInputGUI(self::POST_DOMAIN_ID);

        $form->addItem($domain_id);
        return $form;
    }
}
