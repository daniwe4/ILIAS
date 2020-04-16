<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\TrainingSearch\Settings;

class ilTrainingSearchSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_IS_ONLINE = "f_is_online";
    const F_IS_LOCAL = "f_is_local";
    const F_RELEVANT_TOPICS = "f_relevant_topics";
    const F_RELEVANT_CATEGORIES = "f_relevant_categories";
    const F_RELEVANT_TARGET_GROUPS = "f_relevant_target_groups";
    const F_IS_RECOMMENDATION = "f_is_recommendation";
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \Closure
     */
    protected $txt_closure;

    /**
     * @var \ilObjTrainingSearch
     */
    protected $object;

    public function __construct(
        \ilCtrl $ctrl,
        \ilTemplate $tpl,
        \ilObjTrainingSearch $object
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt_closure = $object->getTxtClosure();
        $this->object = $object;
    }

    /**
     * @throws Exception if cmd is unknown
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->showSettingsForm();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveSettings();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showSettingsForm(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->initForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showSettingsForm($form);
            return;
        }

        $post = $_POST;
        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];

        $is_online = false;
        if (isset($post[self::F_IS_ONLINE]) &&
            $post[self::F_IS_ONLINE] == 1
        ) {
            $is_online = true;
        }

        $is_local = false;
        if (isset($post[self::F_IS_LOCAL]) &&
            $post[self::F_IS_LOCAL] == 1
        ) {
            $is_local = true;
        }
        $topics = [];
        $categories = [];
        $target_groups = [];
        if (ilPluginAdmin::isPluginActive('xccl')) {
            if (array_key_exists(self::F_RELEVANT_TOPICS, $post)) {
                $topics = array_map(function ($int) {
                    return (int) $int;
                }, $post[self::F_RELEVANT_TOPICS]);
            }
            if (array_key_exists(self::F_RELEVANT_CATEGORIES, $post)) {
                $categories = array_map(function ($int) {
                    return (int) $int;
                }, $post[self::F_RELEVANT_CATEGORIES]);
            }
            if (array_key_exists(self::F_RELEVANT_TARGET_GROUPS, $post)) {
                $target_groups = array_map(function ($int) {
                    return (int) $int;
                }, $post[self::F_RELEVANT_TARGET_GROUPS]);
            }
        }

        $is_recommendation = false;
        if (isset($post[self::F_IS_RECOMMENDATION]) &&
            $post[self::F_IS_RECOMMENDATION] == 1
        ) {
            $is_recommendation = true;
        }

        $fnc = function (Settings\Settings $s) use (
            $is_online,
            $is_local,
            $is_recommendation,
            $topics,
            $categories,
            $target_groups
        ) {
            $s = $s->withIsOnline($is_online)
                ->withIsLocal($is_local)
                ->withIsRecommendationAllowed($is_recommendation)
            ;

            if (ilPluginAdmin::isPluginActive('xccl')) {
                $s = $s->withRelevantTopics($topics)
                    ->withRelevantCategories($categories)
                    ->withRelevantTargetGroups($target_groups)
                ;
            }

            return $s;
        };

        $this->object->updateSettings($fnc);

        $this->object->setTitle($title);
        $this->object->setDescription($description);
        $this->object->update();

        ilUtil::sendSuccess($this->txt("settings_saved"), true);
        $this->ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    protected function initForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("title"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $ti = new \ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new \ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $cbx = new \ilCheckboxInputGUI($this->txt("is_online"), self::F_IS_ONLINE);
        $cbx->setValue(1);
        $form->addItem($cbx);

        $cbx = new \ilCheckboxInputGUI($this->txt("is_local"), self::F_IS_LOCAL);
        $cbx->setValue(1);
        $form->addItem($cbx);

        $cbx = new \ilCheckboxInputGUI(
            $this->txt("is_recommendation_allowed"),
            self::F_IS_RECOMMENDATION
        );
        $cbx->setValue(1);
        $form->addItem($cbx);

        if (ilPluginAdmin::isPluginActive('xccl')) {
            $plugin = ilPluginAdmin::getPluginObjectById('xccl');
            $actions = $plugin->getActions();
            $topics = new \ilMultiSelectInputGUI(
                $this->txt('relevant_topics'),
                self::F_RELEVANT_TOPICS
            );
            $topics->setOptions($actions->getTopicOptions());
            $form->addItem($topics);

            $categories = new \ilMultiSelectInputGUI(
                $this->txt('relevant_categories'),
                self::F_RELEVANT_CATEGORIES
            );
            $categories->setOptions($actions->getCategoryOptions());
            $form->addItem($categories);

            $target_groups = new \ilMultiSelectInputGUI(
                $this->txt("relevant_target_groups"),
                self::F_RELEVANT_TARGET_GROUPS
            );
            $target_groups->setOptions($actions->getTargetGroupOptions());
            $form->addItem($target_groups);
        }

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        return $form;
    }

    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $values = $this->getFormValues();
        $form->setValuesByArray($values);
    }

    /**
     * @return mixed[]
     */
    protected function getFormValues() : array
    {
        $settings = $this->object->getSettings();

        $values = [];
        $values[self::F_TITLE] = $this->object->getTitle();
        $values[self::F_DESCRIPTION] = $this->object->getDescription();
        $values[self::F_IS_ONLINE] = $settings->getIsOnline();
        $values[self::F_IS_LOCAL] = $settings->isLocal();
        $values[self::F_RELEVANT_TOPICS] = $settings->relevantTopics();
        $values[self::F_RELEVANT_CATEGORIES] = $settings->relevantCategories();
        $values[self::F_RELEVANT_TARGET_GROUPS] = $settings->relevantTargetGroups();
        $values[self::F_IS_RECOMMENDATION] = $settings->isRecommendationAllowed();

        return $values;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt_closure;
        return $txt($code);
    }
}
