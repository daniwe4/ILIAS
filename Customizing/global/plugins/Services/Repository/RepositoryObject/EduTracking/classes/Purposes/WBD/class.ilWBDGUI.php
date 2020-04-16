<?php

use CaT\Plugins\EduTracking\Purposes\WBD;
use CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ConfigWBD as CWBD;

/**
 * Repository configuration for WBD purpose
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilWBDGUI
{
    const CMD_EDIT = "editWBD";
    const CMD_SAVE = "saveWBD";

    const F_EDUCATION = "f_education";
    const F_EDUCATION_TYPE = "f_education_type";
    const F_EDUCATION_CONTENT = "f_education_content";
    const F_CRS_START = "f_crs_start";
    const F_CRS_END = "f_crs_end";
    const F_INTERNAL_ID = "f_internal_id";
    const F_CONTACT_TITLE = "f_contact_title";
    const F_CONTACT_FIRSTNAME = "f_contact_firstname";
    const F_CONTACT_LASTNAME = "f_contact_lastname";
    const F_CONTACT_PHONE = "f_contact_phone";
    const F_CONTACT_MAIL = "f_contact_mail";

    private static $education_types = array(
        "001" => "Präsenzveranstaltung",
        "002" => "Einzeltraining",
        "003" => "Blended Learning",
        "004" => "gesteuertes E-Learning",
        "005" => "selbstgesteuertes E-Learning"
    );

    private static $education_contents = array(
        "001" => "Privat-Vorsorge-Lebens-/Rentenversicherung",
        "002" => "Privat-Vorsorge-Kranken-/Pflegeversicherung",
        "003" => "Privat-Sach-/Schadenversicherung",
        "004" => "Firmenkunden-Vorsorge(bAV/Personenversicherung)",
        "005" => "Firmenkunden-Sach-/Schadenversicherung",
        "006" => "Spartenübergreifend",
        "007" => "Beratungskompetenz"
    );

    /**
     * @var ilEduTrackingSettingsGUI
     */
    protected $parent;

    /**
     * @var WBD\ilObjActions
     */
    protected $actions;

    /**
     * @var WBD\Configuration\ilActions
     */
    protected $config_actions;

    /**
     * @var ilObjCourse
     */
    protected $parent_course;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @param bool 	$below_course
     */
    public function __construct(
        ilEduTrackingSettingsGUI $parent,
        WBD\ilActions $actions,
        WBD\Configuration\ilActions $config_actions,
        ilObjCourse $parent_course = null
    ) {
        $this->parent = $parent;
        $this->actions = $actions;
        $this->parent_course = $parent_course;
        $this->config_actions = $config_actions;
        $this->below_course = $parent_course !== null;


        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tree = $DIC->repositoryTree();
        $this->g_objDefinition = $DIC["objDefinition"];
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT:
                $this->edit();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Edit current settings
     *
     * @param ilPropertyFormGUI | null 	$form
     *
     * @return void
     */
    protected function edit(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT, $this->txt("cancel"));

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save current settings
     *
     * @return void
     */
    protected function save()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->edit($form);
            return;
        }

        $post = $_POST;

        $education_type = $post[self::F_EDUCATION_TYPE];
        $education_content = $post[self::F_EDUCATION_CONTENT];

        $settings = $this->actions->select();
        $settings = $settings->withEducationType($education_type)
            ->withEducationContent($education_content);

        $settings->update();

        ilUtil::sendSuccess($this->txt("wbd_settings_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT);
    }

    /**
     * Init the form if object is below course
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        $form = $this->getForm();

        $ne = new ilNonEditableValueGUI($this->txt("education"), self::F_EDUCATION);
        $form->addItem($ne);

        $si = new ilSelectInputGUI($this->txt("education_type"), self::F_EDUCATION_TYPE);
        $si->setRequired(true);
        $options = array(null => $this->txt("please_select"));
        foreach (self::$education_types as $value => $education_type) {
            $options[$value] = $education_type;
        }
        $si->setOptions($options);
        $form->addItem($si);

        $si = new ilSelectInputGUI($this->txt("education_content"), self::F_EDUCATION_CONTENT);
        $si->setRequired(true);
        $options = array(null => $this->txt("please_select"));
        foreach (self::$education_contents as $value => $education_content) {
            $options[$value] = $education_content;
        }
        $si->setOptions($options);
        $form->addItem($si);

        $ne = new ilNonEditableValueGUI($this->txt("crs_start"), self::F_CRS_START);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt("crs_end"), self::F_CRS_END);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt("internal_id"), self::F_INTERNAL_ID);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt("contact_title"), self::F_CONTACT_TITLE);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt("contact_firstname"), self::F_CONTACT_FIRSTNAME);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt("contact_lastname"), self::F_CONTACT_LASTNAME);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt("contact_phone"), self::F_CONTACT_PHONE);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt("contact_mail"), self::F_CONTACT_MAIL);
        $form->addItem($ne);

        return $form;
    }

    /**
     * Fills form with values
     *
     * @param ilPropertyFormGUI
     *
     * @return void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();

        $wbd_data_interface = new CaT\Plugins\EduTracking\Purposes\WBD\WBDDataInterface(
            $this->actions->select(),
            $this->config_actions->select(),
            new WBD\IliasWBDUserDataProvider(),
            new WBD\IliasWBDObjectProvider($this->g_tree)
        );

        $title = $wbd_data_interface->getContactTitle();

        if (!$wbd_data_interface->contactProvided()) {
            switch ($wbd_data_interface->getContactType()) {
                case CWBD::M_FIX_CONTACT:
                    $title = $this->txt("no_user_defined");
                    break;
                case CWBD::M_COURSE_TUTOR:
                    $title = $this->txt("contact_informations_from_tutor");
                    break;
                case CWBD::M_COURSE_ADMIN:
                    $title = $this->txt("contact_informations_from_admin");
                    break;
                case CWBD::M_XCCL_CONTACT:
                    $title = $this->txt("contact_informations_from_xccl");
                    break;
            }
        }


        $firstname = $wbd_data_interface->getContactFirstname();
        $lastname = $wbd_data_interface->getContactLastname();
        $email = $wbd_data_interface->getContactEmail();
        $phone = $wbd_data_interface->getContactPhone();


        $values[self::F_EDUCATION] = $this->txt("crs_title");
        $values[self::F_EDUCATION_TYPE] = $wbd_data_interface->getEducationType();
        $values[self::F_EDUCATION_CONTENT] = $wbd_data_interface->getEducationContent();
        $values[self::F_CRS_START] = $this->txt("booking_date_of_user");
        $values[self::F_CRS_END] = $this->txt("finish_date_of_user");
        $values[self::F_INTERNAL_ID] = $this->txt("crs_ref_id");
        $values[self::F_CONTACT_TITLE] = $title;
        $values[self::F_CONTACT_FIRSTNAME] = $firstname;
        $values[self::F_CONTACT_LASTNAME] = $lastname;
        $values[self::F_CONTACT_PHONE] = $phone;
        $values[self::F_CONTACT_MAIL] = $mail;

        $values[self::F_EDUCATION] = $wbd_data_interface->getCourseTitle();
        $crs_start = $wbd_data_interface->getStartDate();
        if ($crs_start !== null) {
            $values[self::F_CRS_START] = $crs_start->format('d.m.Y');
        }

        $crs_end = $wbd_data_interface->getEndDate();
        if ($crs_end !== null) {
            $values[self::F_CRS_END] = $crs_end->format('d.m.Y');
            ;
        }

        $internal_id = $wbd_data_interface->getInternalId();
        if ($this->parent_course !== null) {
            $internal_id = str_replace(
                '{REF_ID}',
                $this->parent_course->getRefId(),
                $internal_id
            );
        }
        $values[self::F_INTERNAL_ID] = $internal_id;

        $form->setValuesByArray($values);
    }


    /**
     * Get form with basic configuration
     *
     * @return ilPropertyFormGUI
     */
    protected function getForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->setTitle($this->txt("wbd_settings"));

        return $form;
    }


    /**
     * @param 	string	$code
     * @return	string
     */
    protected function txt($code)
    {
        return $this->actions->getObject()->pluginTxt($code);
    }
}
