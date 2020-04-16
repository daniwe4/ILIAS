<?php

use CaT\Plugins\CourseClassification\ilActions;
use CaT\Plugins\CourseClassification\AdditionalLinks\ilAdditionalLinkInputGUI;

class ilCourseClassificationGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SHOW_CONTENT = "showContent";
    const CMD_SAVE = "saveProperties";
    const CMD_CANCEL = "cancel";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_TYPE = "f_type";
    const F_TOPICS = "f_topics";
    const F_EDU_PROGRAM = "f_edu_program";
    const F_CONTENT = "f_content";
    const F_GOALS = "f_goals";
    const F_PREPARATION = "f_preparation";
    const F_METHOD = "f_method";
    const F_MEDIA = "f_media";
    const F_TARGET_GROUP = "f_target_group";
    const F_TARGET_GROUP_DESCRIPTION = "f_target_group_description";
    const F_CONTACT_NAME = "f_contact_name";
    const F_CONTACT_RESPONSIBILITY = "f_contact_responsibility";
    const F_CONTACT_PHONE = "f_contact_phone";
    const F_CONTACT_MAIL = "f_contact_mail";
    const F_ADDITIONAL_LINKS = "f_additional_links";

    const TAB_SETTINGS = "tab_settings";
    const TAB_CONTENT = "tab_content";

    const JSON_LINK = "json_link";

    /**
     * @var ilObjCourseClassificationGUI
     */
    protected $parent_gui;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
<<<<<<< HEAD
     * @var ilGlobalTemplateInterface
=======
     * @var ilTemplate
>>>>>>> TMS CourseClassification: Add Plugin to MonoRepo and update unit tests
     */
    protected $g_tpl;

    public function __construct(ilObjCourseClassificationGUI $parent_gui, ilActions $actions)
    {
        global $DIC;

        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
        $this->txt = $actions->getObject()->txtClosure();

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $plugin_dir = $this->actions->getObject()->getDirectory();
        $this->g_tpl->addJavaScript($plugin_dir . "/templates/js/xccl_load_categories.js");
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_CONTENT);
        switch ($cmd) {
            case self::CMD_CANCEL:
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE:
                $this->saveProperties();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    /**
     * Show editable settings
     *
     * @param ilPropertyFormGUI 	$form
     *
     * @return null
     */
    protected function editProperties($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save changes of properties
     *
     * @return null
     */
    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProperties($form);
            return;
        }

        $object = $this->actions->getObject();

        $post = $_POST;


        $object->setTitle($post[self::F_TITLE]);
        $object->setDescription($post[self::F_DESCRIPTION]);

        $additional_links_input = $form->getItemByPostVar(self::F_ADDITIONAL_LINKS);
        $additional_links = $additional_links_input->retrieveValuesFromPost($post);

        $update = function ($course_classification) use ($post, $additional_links) {
            $contact = $course_classification->getContact();
            $contact = $contact->withName($post[self::F_CONTACT_NAME])
                               ->withResponsibility($post[self::F_CONTACT_RESPONSIBILITY])
                               ->withPhone($post[self::F_CONTACT_PHONE])
                               ->withMail($post[self::F_CONTACT_MAIL]);

            $topics = $this->transformMultiSelectPostValue($post[self::F_TOPICS]);
            $categories = $this->actions->getCategoriesByTopicIds($topics);
            return $course_classification->withType((int) $post[self::F_TYPE])
                                         ->withEduProgram((int) $post[self::F_EDU_PROGRAM])
                                         ->withCategories($categories)
                                         ->withTopics($topics)
                                         ->withContent($post[self::F_CONTENT])
                                         ->withGoals($post[self::F_GOALS])
                                         ->withPreparation($post[self::F_PREPARATION])
                                         ->withMethod($this->transformMultiSelectPostValue($post[self::F_METHOD]))
                                         ->withMedia($this->transformMultiSelectPostValue($post[self::F_MEDIA]))
                                         ->withTargetGroup($this->transformMultiSelectPostValue($post[self::F_TARGET_GROUP]))
                                         ->withTargetGroupDescription($post[self::F_TARGET_GROUP_DESCRIPTION])
                                         ->withContact($contact)
                                         ->withAdditionalLinks($additional_links);
        };

        $object->updateCourseClassification($update);
        $object->update();

        ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    /**
     * Init properties form
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        $form = new ilPropertyFormGUI();

        if (!$readonly) {
            $form->setFormAction($this->g_ctrl->getFormAction($this));
            $form->setTitle($this->txt("settings_form_title"));
            $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
            $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        } else {
            $form->setTitle($this->txt("content_form_title"));
        }

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ti);

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("informations"));
        $form->addItem($title_section);

        $si = new ilSelectInputGUI($this->txt("type"), self::F_TYPE);
        $options = array(null => $this->txt("pls_select")) + $this->sortArray($this->actions->getTypeOptions());
        $si->setOptions($options);
        $form->addItem($si);

        $si = new ilSelectInputGUI($this->txt("edu_program"), self::F_EDU_PROGRAM);
        $options = array(null => $this->txt("pls_select")) + $this->sortArray($this->actions->getEduProgramOptions());
        $si->setOptions($options);
        $form->addItem($si);

        $ms = new ilMultiSelectInputGUI($this->txt("topic"), self::F_TOPICS);
        $ms->setWidthUnit("%");
        $ms->setWidth(100);
        $ms->setHeightUnit("px");
        $ms->setHeight(140);
        $ms->setOptions($this->sortArray($this->actions->getTopicOptions()));
        $form->addItem($ms);

        $ti = new ilTextAreaInputGUI($this->txt("content"), self::F_CONTENT);
        $ti->setRows(5);
        $form->addItem($ti);

        $ti = new ilTextAreaInputGUI($this->txt("goals"), self::F_GOALS);
        $ti->setRows(5);
        $form->addItem($ti);

        $ti = new ilTextAreaInputGUI($this->txt("preparation"), self::F_PREPARATION);
        $ti->setRows(5);
        $form->addItem($ti);

        $ms = new ilMultiSelectInputGUI($this->txt("method"), self::F_METHOD);
        $ms->setWidthUnit("%");
        $ms->setWidth(100);
        $ms->setHeightUnit("px");
        $ms->setHeight(140);
        $ms->setOptions($this->sortArray($this->actions->getMethodOptions()));
        $form->addItem($ms);

        $ms = new ilMultiSelectInputGUI($this->txt("media"), self::F_MEDIA);
        $ms->setWidthUnit("%");
        $ms->setWidth(100);
        $ms->setHeightUnit("px");
        $ms->setHeight(140);
        $ms->setOptions($this->sortArray($this->actions->getMediaOptions()));
        $form->addItem($ms);

        $ms = new ilMultiSelectInputGUI($this->txt("target_groups"), self::F_TARGET_GROUP);
        $ms->setWidthUnit("%");
        $ms->setWidth(100);
        $ms->setHeightUnit("px");
        $ms->setHeight(140);
        $ms->setOptions($this->sortArray($this->actions->getTargetGroupOptions()));
        $form->addItem($ms);

        $ta = new ilTextAreaInputGUI($this->txt("target_group_description"), self::F_TARGET_GROUP_DESCRIPTION);
        $ta->setRows(5);
        $form->addItem($ta);

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("additional_links"));
        $form->addItem($title_section);

        $labels = ['title', 'url'];
        $links = new ilAdditionalLinkInputGUI($this->txt("additional_links"), self::F_ADDITIONAL_LINKS, $labels);
        $form->addItem($links);

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("contact"));
        $form->addItem($title_section);

        $ti = new ilTextInputGUI($this->txt("name"), self::F_CONTACT_NAME);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("responsibility"), self::F_CONTACT_RESPONSIBILITY);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("phone"), self::F_CONTACT_PHONE);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("mail"), self::F_CONTACT_MAIL);
        $ti->setInfo($this->txt("mail_info"));
        $form->addItem($ti);

        return $form;
    }

    /**
     * Fill the form with current values
     *
     * @param ilPropertyFormGUI 	$form
     *
     * @return null
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $object = $this->actions->getObject();

        $values = array();
        $values[self::F_TITLE] = $object->getTitle();
        $values[self::F_DESCRIPTION] = $object->getDescription();

        $course_classification = $object->getCourseClassification();
        $values[self::F_TYPE] = $course_classification->getType();
        $values[self::F_EDU_PROGRAM] = $course_classification->getEduProgram();
        $values[self::F_TOPICS] = $course_classification->getTopics();
        $values[self::F_CONTENT] = $course_classification->getContent();
        $values[self::F_GOALS] = $course_classification->getGoals();
        $values[self::F_PREPARATION] = $course_classification->getPreparation();
        $values[self::F_METHOD] = $course_classification->getMethod();
        $values[self::F_MEDIA] = $course_classification->getMedia();
        $values[self::F_TARGET_GROUP] = $course_classification->getTargetGroup();
        $values[self::F_TARGET_GROUP_DESCRIPTION] = $course_classification->getTargetGroupDescription();

        $contact = $course_classification->getContact();
        $values[self::F_CONTACT_NAME] = $contact->getName();
        $values[self::F_CONTACT_RESPONSIBILITY] = $contact->getResponsibility();
        $values[self::F_CONTACT_PHONE] = $contact->getPhone();
        $values[self::F_CONTACT_MAIL] = $contact->getMail();

        $values[self::F_ADDITIONAL_LINKS . '_key'] = [];
        $values[self::F_ADDITIONAL_LINKS . '_value'] = [];
        $links = $course_classification->getAdditionalLinks();
        foreach ($links as $link) {
            $values[self::F_ADDITIONAL_LINKS . '_key'][] = $link->getLabel();
            $values[self::F_ADDITIONAL_LINKS . '_value'][] = $link->getUrl();
        }

        $form->setValuesByArray($values);
    }

    /**
     * Transform multiselect post array
     *
     * @param sting[] | null 	$post_array
     *
     * @return int[]
     */
    protected function transformMultiSelectPostValue($post_array)
    {
        if ($post_array === null) {
            return $post_array;
        }

        return array_map(function ($part) {
            return (int) $part;
        }, $post_array);
    }

    /**
     * Sort option via strcasecmp
     *
     * @param string[]
     *
     * @return string[]
     */
    protected function sortArray(array $values)
    {
        uasort($values, function ($a, $b) {
            return strcasecmp($a, $b);
        });

        return $values;
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt($code)
    {
        assert('is_string($code)');
        $txt = $this->txt;

        return $txt($code);
    }
}
