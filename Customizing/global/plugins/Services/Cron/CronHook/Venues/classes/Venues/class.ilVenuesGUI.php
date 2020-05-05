<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use CaT\Plugins\Venues\Venues;
use CaT\Plugins\Venues\Tags;
use CaT\Plugins\Venues\ilActions;

/**
 * GUI class for venue configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilVenuesGUI
{
    const F_VENUE_FILTER = "venueFilter";

    const CMD_SHOW = "showTable";
    const CMD_NEW_VENUE = "newVenue";
    const CMD_ADD_VENUE = "addVenue";
    const CMD_EDIT_VENUE = "editVenue";
    const CMD_UPDATE_VENUE = "updateVenue";
    const CMD_DEL_VENUE = "deleteVenue";
    const CMD_DEL_CONFIRM_VENUE = "deleteConfirmVenue";
    const CMD_APPLY_FILTER = "applyFilter";
    const CMD_RESET_FILTER = "resetFilter";

    const F_TAG_FILTER = "f_tag_filter";
    const ROOT_REF_ID = 1;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var Venues\Address\FormHelper
     */
    protected $address_form_helper;

    /**
     * @var Venues\Capacity\FormHelper
     */
    protected $capacity_form_helper;

    /**
     * @var Venues\Conditions\FormHelper
     */
    protected $conditions_form_helper;

    /**
     * @var Venues\Contact\FormHelper
     */
    protected $contact_form_helper;

    /**
     * @var Venues\Costs\FormHelper
     */
    protected $costs_form_helper;

    /**
     * @var Venues\General\FormHelper
     */
    protected $general_form_helper;

    /**
     * @var Venues\Rating\FormHelper
     */
    protected $rating_form_helper;

    /**
     * @var Venues\Service\FormHelper
     */
    protected $service_form_helper;

    /**
     * @var Tags\Venue\DB
     */
    protected $tags_db;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        ilTree $tree,
        ilActions $actions,
        \Closure $txt,
        Venues\Address\FormHelper $address_form_helper,
        Venues\Capacity\FormHelper $capacity_form_helper,
        Venues\Conditions\FormHelper $conditions_form_helper,
        Venues\Contact\FormHelper $contact_form_helper,
        Venues\Costs\FormHelper $costs_form_helper,
        Venues\General\FormHelper $general_form_helper,
        Venues\Rating\FormHelper $rating_form_helper,
        Venues\Service\FormHelper $service_form_helper,
        Tags\Venue\DB $tags_db
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->tree = $tree;

        $this->txt = $txt;
        $this->actions = $actions;
        $this->address_form_helper = $address_form_helper;
        $this->capacity_form_helper = $capacity_form_helper;
        $this->conditions_form_helper = $conditions_form_helper;
        $this->contact_form_helper = $contact_form_helper;
        $this->general_form_helper = $general_form_helper;
        $this->rating_form_helper = $rating_form_helper;
        $this->service_form_helper = $service_form_helper;
        $this->costs_form_helper = $costs_form_helper;
        $this->tags_db = $tags_db;

        $this->determineFilterValues();

        $this->tpl->addJavaScript($this->actions->getPlugin()->getDirectory() . "/templates/js/LoadMap.js");
    }

    /**
     * @inheritdoc
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW:
                $this->showTable();
                break;
            case self::CMD_EDIT_VENUE:
                $venue_id = $this->getVenueId();
                $this->editVenueForm($venue_id);
                break;
            case self::CMD_NEW_VENUE:
                $this->newVenue();
                break;
            case self::CMD_ADD_VENUE:
                $this->addVenue();
                break;
            case self::CMD_DEL_VENUE:
                $this->deleteVenue();
                break;
            case self::CMD_UPDATE_VENUE:
                $this->updateVenue();
                break;
            case self::CMD_DEL_CONFIRM_VENUE:
                $this->deleteConfirmVenue();
                break;
            case self::CMD_APPLY_FILTER:
                $this->applyFilter();
                break;
            case self::CMD_RESET_FILTER:
                $this->resetFilter();
                break;
        }
    }

    protected function showTable()
    {
        $this->setToolbar();

        $table = new Venues\ilVenuesTableGUI($this, $this->actions, $this->txt, $this->tags_filter_value, self::CMD_SHOW);
        $table->setFilterValues(array(self::F_TAG_FILTER => $this->tags_filter_value));
        $this->fillFilterItem($table);
        $this->tpl->setContent($table->getHtml());
    }

    protected function newVenue(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
        }

        $form->setTitle($this->txt("new_venue_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->addCommandButton(self::CMD_ADD_VENUE, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW, $this->txt("cancel"));

        $this->tpl->setContent($form->getHtml());
    }

    protected function addVenue()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->newVenue($form);
            return;
        }

        $post = $_POST;

        if (!$this->checkVenueValues($form, $post)) {
            $form->setValuesByPost();
            $this->newVenue($form);
            return;
        }

        if ($this->general_form_helper->checkName($form, $post)) {
            $form->setValuesByPost();
            $this->newVenue($form);
            return;
        }

        $next_id = $this->actions->getNewVenueId();
        $this->general_form_helper->createObject($next_id, $post);
        $this->rating_form_helper->createObject($next_id, $post);
        $this->address_form_helper->createObject($next_id, $post);
        $this->contact_form_helper->createObject($next_id, $post);
        $this->conditions_form_helper->createObject($next_id, $post);
        $this->capacity_form_helper->createObject($next_id, $post);
        $this->service_form_helper->createObject($next_id, $post);
        $this->costs_form_helper->createObject($next_id, $post);

        \ilUtil::sendSuccess($this->txt("venue_added"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function editVenueForm(int $venue_id, ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $values = $this->getVenueValues($venue_id);
            $form->setValuesByArray($values);
        }

        $form->setTitle($this->txt("edit_venue_title"));
        $this->ctrl->setParameter($this, "id", $venue_id);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $this->ctrl->setParameter($this, "id", null);

        $form->addCommandButton(self::CMD_UPDATE_VENUE, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW, $this->txt("cancel"));

        $this->tpl->setContent($form->getHtml());
    }

    protected function updateVenue()
    {
        $form = $this->initForm();
        $venue_id = $this->getVenueId();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editVenueForm($venue_id, $form);
            return;
        }
        $post = $_POST;
        if (!$this->checkVenueValues($form, $post)) {
            $form->setValuesByPost();
            $this->editVenueForm($venue_id, $form);
            return;
        }
        if ($this->general_form_helper->checkNameChanged($form, $post)) {
            $form->setValuesByPost();
            $this->editVenueForm($venue_id, $form);
            return;
        }
        $general = $this->general_form_helper->getObject($venue_id, $post);
        $rating = $this->rating_form_helper->getObject($venue_id, $post);
        $address = $this->address_form_helper->getObject($venue_id, $post);
        $contact = $this->contact_form_helper->getObject($venue_id, $post);
        $conditions = $this->conditions_form_helper->getObject($venue_id, $post);
        $capacity = $this->capacity_form_helper->getObject($venue_id, $post);
        $service = $this->service_form_helper->getObject($venue_id, $post);
        $costs = $this->costs_form_helper->getObject($venue_id, $post);

        $this->actions->update(
            $general,
            $rating,
            $address,
            $contact,
            $conditions,
            $capacity,
            $service,
            $costs
        );

        $crs_ids = $this->actions->getAffectedCrsObjIds($venue_id);
        $this->actions->throwEvent($crs_ids);

        \ilUtil::sendSuccess($this->txt("venue_updated"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function deleteConfirmVenue()
    {
        $id = (int) $_GET["id"];
        if ($this->actions->isUsed($id) || $this->usedInAccomodation($id)) {
            ilUtil::sendInfo($this->txt("venue_is_in_use"), true);
            $this->ctrl->redirect($this);
        }

        $confirmation = new \ilConfirmationGUI();

        $confirmation->setFormAction($this->ctrl->getFormAction($this, self::CMD_DEL_VENUE));
        $confirmation->setHeaderText($this->txt("confirm_delete_venue"));
        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW);
        $confirmation->setConfirm($this->txt("delete_venue"), self::CMD_DEL_VENUE);

        $confirmation->addHiddenItem("id", $id);
        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function usedInAccomodation(int $id) : bool
    {
        if (ilPluginAdmin::isPluginActive('xoac')) {
            $accomodations = $this->getAllChildrenOfByType(self::ROOT_REF_ID, "xoac");
            foreach ($accomodations as $ac) {
                if ($ac->getObjSettings()->getLocationObjId() == $id) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function deleteVenue()
    {
        $id = (int) $_POST["id"];

        $crs_ids = $this->actions->getAffectedCrsObjIds($id);
        $this->tags_db->deleteAllocationByVenueId($id);
        $this->actions->removeVenueBy($id);
        $this->actions->throwEvent($crs_ids);

        $this->ctrl->redirect($this);
    }

    /**
     * Initilaize venue input form
     *
     * @return \ilPropertyFormGUI
     */
    protected function initForm() : ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $this->getAddNewFormItems($form);
        return $form;
    }

    /**
     * @inheritdoc
     */
    protected function setToolbar()
    {
        $this->toolbar->addButton($this->txt("new_venue"), $this->ctrl->getLinkTargetByClass(array("ilVenuesConfigGUI", "ilVenuesGUI"), self::CMD_NEW_VENUE));
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    public function getAddNewFormItems(\ilPropertyFormGUI $form)
    {
        $this->general_form_helper->addFormItems($form);
        $this->rating_form_helper->addFormItems($form);
        $this->address_form_helper->addFormItems($form);
        $this->contact_form_helper->addFormItems($form);
        $this->conditions_form_helper->addFormItems($form);
        $this->capacity_form_helper->addFormItems($form);
        $this->service_form_helper->addFormItems($form);
        $this->costs_form_helper->addFormItems($form);
    }

    public function checkVenueValues(\ilPropertyFormGUI $form, array &$post) : bool
    {
        return $this->general_form_helper->checkValues($form, $post) && $this->address_form_helper->checkValues($form, $post);
    }

    protected function fillFilterItem(Venues\ilVenuesTableGUI $table)
    {
        $si = new \ilMultiSelectInputGUI($this->txt("tags"), self::F_TAG_FILTER);
        $si->setWidth(350);
        $si->setOptions($this->getFilterTagOptions());
        $si->setValue($this->tags_filter_value);

        $table->addFilterItem($si);
    }

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
     * @return string[]
     */
    protected function getTagOptions() : array
    {
        $tag_options = array();
        foreach ($this->tags_db->getTagsRaw() as $key => $tag) {
            $tag_options[$tag["id"]] = $tag["name"];
        }
        return $tag_options;
    }

    /**
     * @return string[]
     */
    protected function getFilterTagOptions() : array
    {
        $tag_options = array();
        foreach ($this->tags_db->getAssignedTagsRaw() as $key => $tag) {
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

        $this->showTable();
    }

    protected function resetFilter()
    {
        $this->tags_filter_value = array();
        $this->showTable();
    }

    protected function getVenueValues(int $venue_id) : array
    {
        $venue = $this->actions->getVenue($venue_id);

        $values = array();
        $this->general_form_helper->addValues($values, $venue);
        $this->rating_form_helper->addValues($values, $venue);
        $this->address_form_helper->addValues($values, $venue);
        $this->contact_form_helper->addValues($values, $venue);
        $this->conditions_form_helper->addValues($values, $venue);
        $this->capacity_form_helper->addValues($values, $venue);
        $this->service_form_helper->addValues($values, $venue);
        $this->costs_form_helper->addValues($values, $venue);

        return $values;
    }

    protected function getVenueId() : int
    {
        $get = $_GET;
        if (!isset($get["id"])) {
            ilUtil::sendFailure($this->txt("no_venue_id"), true);
            $this->ctrl->redirect($this);
        }
        return (int) $get["id"];
    }

    /**
     * @return Object[] 	of search type
     */
    protected function getAllChildrenOfByType(int $ref_id, string $search_type) : array
    {
        $children = $this->tree->getSubTree(
            $this->tree->getNodeData($ref_id),
            true,
            $search_type
        );

        return array_map(
            function ($node) {
                return ilObjectFactory::getInstanceByRefId($node["child"]);
            },
            $children
        );
    }
}
