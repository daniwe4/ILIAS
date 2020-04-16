<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues;

use CaT\Plugins\Venues\ilActions;

/**
 * Table GUI for venue configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilVenuesTableGUI extends \ilTable2GUI
{
    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var ILIAS\UI\Implementation\Factory
     */
    protected $g_f;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $g_renderer;

    /**
     * @var \ILIAS\Data\Factory
     */
    protected $df;

    /**
     * @param string 	$a_parent_cmd
     * @param string 	$a_template_context
     */
    public function __construct(
        \ilVenuesGUI $parent_object,
        ilActions $actions,
        \Closure $txt,
        array $filtered_tags = array(),
        $a_parent_cmd = "",
        $a_template_context = ""
    ) {
        $this->txt = $txt;
        $this->setId("venues");
        parent::__construct($parent_object, $a_parent_cmd, $a_template_contex);

        global $DIC;
        $this->g_f = $DIC->ui()->factory();
        $this->df = new \ILIAS\Data\Factory;
        $this->g_renderer = $DIC->ui()->renderer();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->actions = $actions;

        $this->g_tpl->addCss($this->actions->getPlugin()->getDirectory() . "/templates/default/venue_table.css");

        $this->configureTable();

        $this->determineOffsetAndOrder();
        $order = $this->getOrderField();
        $order_direction = $this->getOrderDirection();
        $data = $this->actions->getAllVenues($order, $order_direction, $filtered_tags);
        $this->setData($data);
    }

    /**
     * @inheritdoc
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("NAME", $a_set->getGeneral()->getName());

        $rating = $a_set->getRating()->getRating();
        for ($i = 0.2; $i <= 1; $i += 0.2) {
            $this->tpl->setCurrentBlock("rating");
            if ((string) $i <= $rating) {
                $this->tpl->setVariable("RATING", \ilUtil::getImagePath("icon_rate_on.svg"));
                $this->tpl->setVariable("ALT", $i);
            } else {
                $this->tpl->setVariable("RATING", \ilUtil::getImagePath("icon_rate_off.svg"));
                $this->tpl->setVariable("ALT", $i);
            }
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set->getCondition()->getGeneralAgreement()) {
            $this->tpl->setVariable("GENERAL_AGREEMENT", $this->txt("yes"));
        } else {
            $this->tpl->setVariable("GENERAL_AGREEMENT", $this->txt("no"));
        }

        $this->tpl->setVariable("ACTIONS", $this->getActionMenu($a_set->getId()));

        foreach ($this->getSelectedColumns() as $column) {
            $this->tpl->setCurrentBlock("selectable_column");
            switch ($column) {
                    case "tags":
                        $value = $this->getTagsHtml($a_set->getGeneral()->getTags());
                        break;
                    case "tags_search":
                        $value = $this->getTagsHtml($a_set->getGeneral()->getSearchTags());
                        break;
                    case "info":
                        $value = $a_set->getRating()->getInfo();
                        break;
                    case "address":
                        $tpl = new \ilTemplate("tpl.address_column.html", true, true, "Customizing/global/plugins/Services/Cron/CronHook/Venues");

                        $address1 = $a_set->getAddress()->getAddress1();
                        if ($address1 != "") {
                            $tpl->setCurrentBlock("address1");
                            $tpl->setVariable("ADRESS1", $address1);
                            $tpl->parseCurrentBlock();
                        }

                        $address2 = $a_set->getAddress()->getAddress2();
                        if ($address2 != "") {
                            $tpl->setCurrentBlock("address2");
                            $tpl->setVariable("ADRESS2", $address2);
                            $tpl->parseCurrentBlock();
                        }

                        $country = $a_set->getAddress()->getCountry();
                        if ($country != "") {
                            $tpl->setCurrentBlock("country");
                            $tpl->setVariable("COUNTRY", $country);
                            $tpl->parseCurrentBlock();
                        }

                        $postcode = $a_set->getAddress()->getPostcode();
                        if ($postcode != "") {
                            $tpl->setCurrentBlock("postcode");
                            $tpl->setVariable("POSTCODE", $postcode);
                            $tpl->parseCurrentBlock();
                        }

                        $city = $a_set->getAddress()->getCity();
                        if ($city != "") {
                            $tpl->setCurrentBlock("city");
                            $tpl->setVariable("CITY", $city);
                            $tpl->parseCurrentBlock();
                        }

                        $value = $tpl->get();
                        break;
                    case "homepage":
                        $value = $a_set->getGeneral()->getHomepage();
                        break;
                    case "internal_contact":
                        $value = $a_set->getContact()->getInternalContact();
                        break;
                    case "contact":
                        $value = $a_set->getContact()->getContact();
                        break;
                    case "phone":
                        $value = $a_set->getContact()->getPhone();
                        break;
                    case "fax":
                        $value = $a_set->getContact()->getFax();
                        break;
                    case "email":
                        $value = $a_set->getContact()->getEmail();
                        break;
                    case "terms":
                        $value = $a_set->getCondition()->getTerms();
                        break;
                    case "valuta":
                        $value = $a_set->getCondition()->getValuta();
                        break;
                    case "number_rooms_overnight":
                        $value = $a_set->getCapacity()->getNumberRoomsOvernights();
                        break;
                    case "min_person_any_room":
                        $value = $a_set->getCapacity()->getMinPersonAnyRoom();
                        break;
                    case "max_person_any_room":
                        $value = $a_set->getCapacity()->getMaxPersonAnyRoom();
                        break;
                    case "room_count":
                        $value = $a_set->getCapacity()->getRoomCount();
                        break;
                    case "min_room_size":
                        $value = $a_set->getCapacity()->getMinRoomSize();
                        break;
                    case "max_room_size":
                        $value = $a_set->getCapacity()->getMaxRoomSize();
                        break;
                    case "mail_service":
                        $value = $a_set->getService()->getMailServiceList();
                        break;
                    case "mail_room_setup":
                        $value = $a_set->getService()->getMailRoomSetup();
                        break;
                    case "mail_material_list":
                        $value = $a_set->getService()->getMailMaterialList();
                        break;
                    case "mail_accomodation_list":
                        $value = $a_set->getService()->getMailAccomodationList();
                        break;
                    case "days_mail_service_list":
                        $value = $a_set->getService()->getDaysSendService();
                        break;
                    case "days_mail_room_setup":
                        $value = $a_set->getService()->getDaysSendRoomSetup();
                        break;
                    case "days_mail_material_list":
                        $value = $a_set->getService()->getDaysSendMaterial();
                        break;
                    case "days_mail_accomodation_list":
                        $value = $a_set->getService()->getDaysSendAccomodation();
                        break;
                    case "days_remind_accomodation_list":
                        $value = $a_set->getService()->getDaysRemindAccomodation();
                        break;
                    case "fixed_rate_day":
                        $value = $a_set->getCosts()->getFixedRateDay();
                        break;
                    case "fixed_rate_all_inclusive":
                        $value = $a_set->getCosts()->getFixedRateAllInclusiv();
                        break;
                    case "bed_and_breakfast":
                        $value = $a_set->getCosts()->getBedAndBreakfast();
                        break;
                    case "other":
                        $value = $a_set->getCosts()->getOther();
                        break;
                    case "costs_terms":
                        $value = $a_set->getCosts()->getTerms();
                        break;
                    case "postcode":
                        $value = $a_set->getAddress()->getPostcode();
                        break;
                    default:
                        $value = "";
            }

            if (is_null($value)) {
                $value = "";
            }
            $this->tpl->setVariable("VALUE", $value);
            $this->tpl->parseCurrentBlock($column);
        }
    }

    /**
     * Sets needed options and columns for the table
     *
     * @return void
     */
    protected function configureTable()
    {
        $this->setEnableTitle(true);
        $this->setTitle($this->txt("venues"));
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(false);
        $this->setRowTemplate("tpl.venue_table_row.html", $this->actions->getPlugin()->getDirectory());
        $this->setShowRowsSelector(false);
        $this->setLimit(0);
        $this->setDefaultOrderField("name");

        $this->setFormAction($this->g_ctrl->getFormAction($this->parent_obj, "configure"));

        $columns = $this->getBaseColumns();

        foreach ($this->getSelectedColumns() as $column) {
            $columns[$column] = array("");
        }

        $columns["actions"] = array("");

        foreach ($columns as $lng_var => $params) {
            $this->addColumn($this->txt($lng_var), $params[0]);
        }
    }

    /**
     * @return array<string, string[]>
     */
    public function getSelectableColumns() : array
    {
        return array(
            "tags" => array("txt" => $this->txt("tags")),
            "tags_search" => array("txt" => $this->txt("tags_search")),
            "info" => array("txt" => $this->txt("info")),
            "address" => array("txt" => $this->txt("address")),
            "postcode" => array("txt" => $this->txt("postcode")),
            "homepage" => array("txt" => $this->txt("homepage")),
            "internal_contact" => array("txt" => $this->txt("internal_contact")),
            "contact" => array("txt" => $this->txt("contact")),
            "phone" => array("txt" => $this->txt("phone")),
            "fax" => array("txt" => $this->txt("fax")),
            "email" => array("txt" => $this->txt("email")),
            "terms" => array("txt" => $this->txt("terms")),
            "valuta" => array("txt" => $this->txt("valuta")),
            "number_rooms_overnight" => array("txt" => $this->txt("number_rooms_overnight")),
            "min_person_any_room" => array("txt" => $this->txt("min_person_any_room")),
            "max_person_any_room" => array("txt" => $this->txt("max_person_any_room")),
            "room_count" => array("txt" => $this->txt("room_count")),
            "min_room_size" => array("txt" => $this->txt("min_room_size")),
            "max_room_size" => array("txt" => $this->txt("max_room_size")),
            "mail_service" => array("txt" => $this->txt("mail_service")),
            "mail_room_setup" => array("txt" => $this->txt("mail_room_setup")),
            "mail_material_list" => array("txt" => $this->txt("mail_material_list")),
            "mail_accomodation_list" => array("txt" => $this->txt("mail_accomodation_list")),
            "days_mail_service_list" => array("txt" => $this->txt("days_mail_service_list")),
            "days_mail_room_setup" => array("txt" => $this->txt("days_mail_room_setup")),
            "days_mail_material_list" => array("txt" => $this->txt("days_mail_material_list")),
            "days_mail_accomodation_list" => array("txt" => $this->txt("days_mail_accomodation_list")),
            "days_remind_accomodation_list" => array("txt" => $this->txt("days_remind_accomodation_list")),
            "fixed_rate_day" => array("txt" => $this->txt("fixed_rate_day")),
            "fixed_rate_all_inclusive" => array("txt" => $this->txt("fixed_rate_all_inclusive")),
            "bed_and_breakfast" => array("txt" => $this->txt("bed_and_breakfast")),
            "other" => array("txt" => $this->txt("other")),
            "costs_terms" => array("txt" => $this->txt("costs_terms"))
        );
    }

    /**
     * @return array<string, string[]>
     */
    protected function getBaseColumns()
    {
        return array("name" => array("name")
                     , "rating" => array("rating")
                     , "general_agreement" => array("")
                );
    }

    protected function getActionMenu(int $id) : string
    {
        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new \ilAdvancedSelectionListGUI();
        $current_selection_list->setAsynch(false);
        $current_selection_list->setAsynchUrl(true);
        $current_selection_list->setListTitle($this->txt("actions"));
        $current_selection_list->setId($id);
        $current_selection_list->setSelectionHeaderClass("small");
        $current_selection_list->setItemLinkClass("xsmall");
        $current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $current_selection_list->setUseImages(false);
        $current_selection_list->setAdditionalToggleElement("id" . $id, "ilContainerListItemOuterHighlight");

        foreach ($this->getActionMenuItems($id) as $key => $value) {
            $current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
        }

        return $current_selection_list->getHTML();
    }

    /**
     * @return array<int, string[]>
     */
    protected function getActionMenuItems(int $id) : array
    {
        $this->g_ctrl->setParameter($this->parent_obj, "id", $id);
        $link_edit = $this->memberlist_link = $this->g_ctrl->getLinkTarget($this->parent_obj, "editVenue");
        $link_delete = $this->memberlist_link = $this->g_ctrl->getLinkTarget($this->parent_obj, "deleteConfirmVenue");
        $this->g_ctrl->clearParameters($this->parent_obj);

        $items = array();
        $items[] = array("title" => $this->txt("edit_venue"), "link" => $link_edit, "image" => "", "frame" => "");
        $items[] = array("title" => $this->txt("delete_venue"), "link" => $link_delete, "image" => "", "frame" => "");

        return $items;
    }

    /**
     * @param Tag[]
     */
    protected function getTagsHtml(array $tags) : string
    {
        $ret = array();
        foreach ($tags as $tag) {
            $color = $this->df->color('#' . $tag->getColorCode());
            $result_tag = $this->g_f->button()->tag($tag->getName(), "#")->withBackgroundColor($color);
            $ret[] = $this->g_renderer->render($result_tag);
        }
        return implode(" ", $ret);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    /**
     * @param mixed[] 	$filter_values
     */
    public function setFilterValues(array $filter_values)
    {
        $this->filter_values = $filter_values;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->g_ctrl->setParameter($this->parent_obj, "filter_values", base64_encode(serialize($this->filter_values)));
        $ret = parent::render();
        $this->g_ctrl->setParameter($this->parent_obj, "filter_values", null);

        return $ret;
    }
}
