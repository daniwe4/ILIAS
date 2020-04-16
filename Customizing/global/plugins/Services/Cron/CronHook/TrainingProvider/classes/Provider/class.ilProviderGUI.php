<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use CaT\Plugins\TrainingProvider\Provider;
use CaT\Plugins\TrainingProvider\ilActions;

/**
 * GUI class for provider configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilProviderGUI
{
    const CMD_CONFIGURE = "configure";
    const CMD_NEW_PROVIDER = "newProvider";
    const CMD_ADD_PROVIDER = "addProvider";
    const CMD_EDIT_PROVIDER = "editProvider";
    const CMD_UPDATE_PROVIDER = "updateProvider";
    const CMD_DEL_PROVIDER = "deleteProvider";
    const CMD_DEL_CONFIRM_PROVIDER = "deleteConfirmProvider";
    const CMD_APPLY_FILTER = "applyFilter";
    const CMD_RESET_FILTER = "resetFilter";

    const F_TAG_FILTER = "f_tag_filter";

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
        $cmd = $this->gCtrl->getCmd(self::CMD_CONFIGURE);

        switch ($cmd) {
            case self::CMD_CONFIGURE:
            case self::CMD_NEW_PROVIDER:
            case self::CMD_ADD_PROVIDER:
            case self::CMD_EDIT_PROVIDER:
            case self::CMD_DEL_PROVIDER:
            case self::CMD_UPDATE_PROVIDER:
            case self::CMD_DEL_CONFIRM_PROVIDER:
            case self::CMD_APPLY_FILTER:
            case self::CMD_RESET_FILTER:
                $this->$cmd();
                break;
        }
    }

    protected function configure()
    {
        $this->setToolbar();

        $table = new Provider\ilProviderTableGUI($this, $this->plugin_object, $this->tags_filter_value);
        $table->setFilterValues(array(self::F_TAG_FILTER => $this->tags_filter_value));
        $this->fillFilterItem($table);
        $this->gTpl->setContent($table->getHtml());
    }

    protected function newProvider($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
        }

        $form->setTitle($this->txt("new_provider_title"));
        $form->setFormAction($this->gCtrl->getFormAction($this));

        $form->addCommandButton(self::CMD_ADD_PROVIDER, $this->txt("save"));
        $form->addCommandButton(self::CMD_CONFIGURE, $this->txt("cancel"));

        $this->gTpl->setContent($form->getHtml());
    }

    protected function addProvider()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->newProvider($form);
            return;
        }

        if (!$this->checkProviderValues($form, $_POST)) {
            $form->setValuesByPost();
            $this->newProvider($form);
            return;
        }

        $post = $_POST;
        $name = $post[ilActions::F_NAME];

        if ($this->nameExists($name)) {
            $form->setValuesByPost();
            $gui = $form->getItemByPostVar(ilActions::F_NAME);
            $gui->setAlert($this->txt("provider_name_exists"));
            $this->newProvider($form);
            return;
        }

        if (isset($post[ilActions::F_RATING]) || trim($post[ilActions::F_RATING]) != "") {
            $rating = floatval(trim($post[ilActions::F_RATING]));
        } else {
            $rating = 0.0;
        }

        if (isset($post[ilActions::F_GENERAL_AGREEMENT]) && $post[ilActions::F_GENERAL_AGREEMENT] == "1") {
            $general_agreement = true;
        } else {
            $general_agreement = false;
        }

        $info = trim($post[ilActions::F_INFO]);
        $address1 = trim($post[ilActions::F_ADDRESS1]);
        $country = trim($post[ilActions::F_COUNTRY]);
        $address2 = trim($post[ilActions::F_ADDRESS2]);
        $postcode = trim($post[ilActions::F_POSTCODE]);
        $city = trim($post[ilActions::F_CITY]);
        $homepage = trim($post[ilActions::F_HOMEPAGE]);
        $internal_contact = trim($post[ilActions::F_INTERNAL_CONTACT]);
        $contact = trim($post[ilActions::F_CONTACT]);
        $phone = trim($post[ilActions::F_PHONE]);
        $fax = trim($post[ilActions::F_FAX]);
        $email = trim($post[ilActions::F_EMAIL]);
        $terms = trim($post[ilActions::F_TERMS]);
        $valuta = trim($post[ilActions::F_VALUTA]);
        $tags = $post[ilActions::F_TAGS];

        $this->actions->createProvider($name, $rating, $info, $address1, $country, $address2, $postcode, $city, $homepage, $internal_contact, $contact, $phone, $fax, $email, $general_agreement, $terms, $valuta, $tags);

        \ilUtil::sendSuccess($this->txt('provider_added'), true);
        $this->gCtrl->redirect($this);
    }

    protected function editProvider()
    {
        if (!isset($_GET["id"])) {
            ilUtil::sendFailure($this->plugin_object->txt("no_provider_id"), true);
            $this->gCtrl->redirect($this);
            return;
        }
        $provider_id = (int) $_GET["id"];

        $this->editProviderForm($provider_id);
    }

    protected function editProviderForm($provider_id, $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $values = $this->actions->getProviderValues($provider_id);
            $form->setValuesByArray($values);
        }

        $form->setTitle($this->txt("edit_provider_title"));
        $this->gCtrl->setParameter($this, "id", $provider_id);
        $form->setFormAction($this->gCtrl->getFormAction($this));
        $this->gCtrl->setParameter($this, "id", null);

        $form->addCommandButton(self::CMD_UPDATE_PROVIDER, $this->txt("save"));
        $form->addCommandButton(self::CMD_CONFIGURE, $this->txt("cancel"));

        $this->gTpl->setContent($form->getHtml());
    }

    protected function updateProvider()
    {
        $form = $this->initForm();
        $id = (int) $_GET["id"];

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProviderForm($id, $form);
            return;
        }

        if (!$this->checkProviderValues($form, $_POST)) {
            $form->setValuesByPost();
            $this->editProviderForm($id, $form);
            return;
        }

        $post = $_POST;
        $name = $post[ilActions::F_NAME];
        $current_name = $this->actions->getCurrentProviderName($id);

        if ($name != $current_name && $this->nameExists($name)) {
            $form->setValuesByPost();
            $gui = $form->getItemByPostVar(ilActions::F_NAME);
            $gui->setAlert($this->txt("provider_name_exists"));
            $this->editProviderForm($id, $form);
            return;
        }

        $this->actions->updateProvider($id, $post);

        $crs_ids = $this->actions->getAffectedCrsObjIds($id);
        $this->actions->throwEvent($crs_ids);

        \ilUtil::sendSuccess($this->txt('provider_updated'), true);
        $this->gCtrl->redirect($this);
    }

    protected function deleteConfirmProvider()
    {
        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI();

        $confirmation->setFormAction($this->gCtrl->getFormAction($this, self::CMD_DEL_PROVIDER));
        $confirmation->setHeaderText($this->txt("confirm_delete_provider"));
        $confirmation->setCancel($this->txt("cancel"), self::CMD_CONFIGURE);
        $confirmation->setConfirm($this->txt("delete_provider"), self::CMD_DEL_PROVIDER);

        $confirmation->addHiddenItem("id", $_GET["id"]);
        $this->gTpl->setContent($confirmation->getHTML());
    }

    protected function deleteProvider()
    {
        $id = $_POST["id"];

        $crs_ids = $this->actions->getAffectedCrsObjIds($id);
        $this->actions->deallocateTagsByProviderId($id);
        $this->actions->removeTrainerByProviderId($id);
        $this->actions->removeProvider($id);
        $this->actions->throwEvent($crs_ids);

        $this->gCtrl->redirect($this);
    }

    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();

        $tag_options = $this->getTagOptions();

        $this->getAddNewFormItems($form, $tag_options);

        return $form;
    }

    protected function setToolbar()
    {
        $this->gToolbar->addButton($this->txt("new_provider"), $this->gCtrl->getLinkTargetByClass(array("ilTrainingProviderConfigGUI", "ilProviderGUI"), self::CMD_NEW_PROVIDER));
    }

    protected function txt($code)
    {
        return $this->plugin_object->txt($code);
    }


    public function getAddNewFormItems($form, $tag_options)
    {
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_overall"));
        $form->addItem($sh);

        $ti = new \ilTextInputGUI($this->txt("name"), ilActions::F_NAME);
        $ti->setInfo(sprintf($this->txt("name_info"), ilActions::NAME_LENGTH));
        $ti->setRequired(true);
        $form->addItem($ti);

        $msi = new \ilMultiSelectInputGUI($this->txt("tags"), ilActions::F_TAGS);
        $msi->setOptions($tag_options);
        $msi->setWidth(975);
        $form->addItem($msi);

        $ti = new \ilTextInputGUI($this->txt("homepage"), ilActions::F_HOMEPAGE);
        $ti->setMaxLength(255);
        $form->addItem($ti);

        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_rating"));
        $form->addItem($sh);

        $si = new \ilSelectInputGUI($this->txt("rating"), ilActions::F_RATING);
        $options = array(null => "-") + $this->getRatingOptions();
        $si->setOptions($options);
        $form->addItem($si);

        $ta = new \ilTextareaInputGUI($this->txt("info"), ilActions::F_INFO);
        $form->addItem($ta);

        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_address"));
        $form->addItem($sh);

        $ti = new \ilTextInputGUI($this->txt("address1"), ilActions::F_ADDRESS1);
        $ti->setInfo(sprintf($this->txt("address1_info"), ilActions::ADDRESS1_LENGTH));
        $ti->setRequired(true);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI(null, ilActions::F_ADDRESS2);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("postcode"), ilActions::F_POSTCODE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("city"), ilActions::F_CITY);
        $ti->setInfo(sprintf($this->txt("city_info"), ilActions::CITY_LENGTH));
        $ti->setRequired(true);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("country"), ilActions::F_COUNTRY);
        $form->addItem($ti);

        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_contact"));
        $form->addItem($sh);

        $ti = new \ilTextInputGUI($this->txt("internal_contact"), ilActions::F_INTERNAL_CONTACT);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("contact"), ilActions::F_CONTACT);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("phone"), ilActions::F_PHONE);
        $ti->setMaxLength(32);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("fax"), ilActions::F_FAX);
        $ti->setMaxLength(32);
        $form->addItem($ti);

        $ti = new \ilEMailInputGUI($this->txt("email"), ilActions::F_EMAIL);
        $ti->setMaxLength(128);
        $form->addItem($ti);

        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_terms"));
        $form->addItem($sh);

        $cb = new \ilCheckboxInputGUI($this->txt("general_agreement"), ilActions::F_GENERAL_AGREEMENT);
        $form->addItem($cb);

        $ta = new \ilTextareaInputGUI($this->txt("terms"), ilActions::F_TERMS);
        $form->addItem($ta);

        $ti = new \ilTextInputGUI($this->txt("valuta"), ilActions::F_VALUTA);
        $ti->setMaxLength(32);
        $form->addItem($ti);
    }

    protected function nameExists($name)
    {
        return $this->actions->providerNameExist($name);
    }

    /**
     * Checks values are legal
     *
     * @param \ilPropertyFormGUI 		$form
     * @param string[] 					$post
     *
     * @return bool
     */
    public function checkProviderValues($form, &$post)
    {
        $ret = true;

        $name = trim($post[ilActions::F_NAME]);
        if (strlen($name) < ilActions::NAME_LENGTH) {
            $gui = $form->getItemByPostVar(ilActions::F_NAME);
            $gui->setAlert($this->txt("provider_name_short"));
            $ret = false;
        }

        $address1 = trim($post[ilActions::F_ADDRESS1]);
        if (strlen($address1) < ilActions::ADDRESS1_LENGTH) {
            $gui = $form->getItemByPostVar(ilActions::F_ADDRESS1);
            $gui->setAlert($this->txt("address1_short"));
            $ret = false;
        }

        $city = trim($post[ilActions::F_CITY]);
        if ($city != "" && strlen($city) < ilActions::CITY_LENGTH) {
            $gui = $form->getItemByPostVar(ilActions::F_CITY);
            $gui->setAlert($this->txt("city_short"));
            $ret = false;
        }

        $homepage = trim($post[ilActions::F_HOMEPAGE]);
        if ($homepage != "" && preg_match(ilActions::HTTP_REGEXP, $homepage) !== 1) {
            $post[ilActions::F_HOMEPAGE] = "https://" . $homepage;
        }

        return $ret;
    }

    /**
     * Get options for rating
     *
     * @return array<foat, string>
     */
    protected function getRatingOptions()
    {
        return array("0.0" => $this->txt("no_stars")
                , "0.2" => $this->txt("one_star")
                , "0.4" => $this->txt("two_stars")
                , "0.6" => $this->txt("three_stars")
                , "0.8" => $this->txt("four_stars")
                , "1.0" => $this->txt("five_stars")
            );
    }

    /**
     * Create table filter
     *
     * @param Provider\ilProviderTableGUI 	$table
     *
     * @return null
     */
    protected function fillFilterItem(Provider\ilProviderTableGUI $table)
    {
        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        $si = new \ilMultiSelectInputGUI($this->txt("tags"), self::F_TAG_FILTER);
        $si->setWidth(350);
        $si->setOptions($this->getFilterTagOptions());
        $si->setValue($this->tags_filter_value);

        $table->addFilterItem($si);
    }

    /**
     * Determin filter values from get
     *
     * @return null
     */
    protected function determineFilterValues()
    {
        $this->tags_filter_value = array();
        $filter_values = $_GET["filter_values"];
        if ($filter_values) {
            $filter_values = unserialize(base64_decode($filter_values));
            $this->tags_filter_value = $filter_values[self::F_TAG_FILTER];
        }
    }

    /**
     * Get tags as options for form
     *
     * @return string[]
     */
    protected function getTagOptions()
    {
        $tag_options = array();
        foreach ($this->actions->getTagsRaw() as $key => $tag) {
            $tag_options[$tag["id"]] = $tag["name"];
        }
        return $tag_options;
    }

    /**
     * Get tags as options for filter
     *
     * @return string[]
     */
    protected function getFilterTagOptions()
    {
        $tag_options = array();
        foreach ($this->actions->getAssignedTagsRaw() as $key => $tag) {
            $tag_options[$tag["id"]] = $tag["name"];
        }
        return $tag_options;
    }

    protected function applyFilter()
    {
        $this->tags_filter_value = array();
        $post = $_POST;
        if (isset($post[self::F_TAG_FILTER]) && $post[self::F_TAG_FILTER] != "") {
            $this->tags_filter_value = $post[self::F_TAG_FILTER];
        }

        $this->configure();
    }

    protected function resetFilter()
    {
        $this->tags_filter_value = array();
        $this->configure();
    }
}
