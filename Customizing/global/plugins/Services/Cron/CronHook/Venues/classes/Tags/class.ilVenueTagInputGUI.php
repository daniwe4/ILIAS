<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */


declare(strict_types=1);

require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
* This class represents a single tag property in a property form.
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version $Id$
*/
class ilVenueTagInputGUI extends ilTextInputGUI
{
    protected $value = array();
    const MINIMUM_LENGTH_NAME = 2;
    const MAXIMUM_LENGTH_NAME = 50;

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
     * @param string 	$a_title
     * @param string 	$a_postvar
     */
    public function __construct(
        string $plugin_dir,
        \Closure $txt,
        string $a_title = "",
        string $a_postvar = ""
    ) {
        parent::__construct($a_title, $a_postvar);
        global $DIC;
        $this->g_f = $DIC->ui()->factory();
        $this->df = new \ILIAS\Data\Factory;
        $this->g_renderer = $DIC->ui()->renderer();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->txt = $txt;
        $this->plugin_dir = $plugin_dir;
    }

    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";

        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
        }
        $foundvalues = $_POST[$this->getPostVar()];

        $names = $foundvalues["name"];
        $colors = $foundvalues["color"];

        $clean_values = array();
        foreach ($names as $key => $name) {
            $clean_values[] = array("name" => $name, "color" => $colors[$key]);
        }

        //Prüfe nur wenn es mindestens einen ausgefüllten Eintrag gibt
        if (!(count($clean_values) == 1 && $clean_values[0]["name"] == "" && $clean_values[0]["color"] == "")) {
            $show_message = false;
            $erro_messages = array();

            foreach ($clean_values as $key => $value) {
                if ($value["name"] === null || strlen($value["name"]) < self::MINIMUM_LENGTH_NAME) {
                    $show_message = true;
                    if (!$erro_messages["message_name_short"]) {
                        $erro_messages["message_name_short"] = sprintf($this->txt("error_name_tag_to_short"), self::MINIMUM_LENGTH_NAME);
                    }
                }

                if ($value["color"] === null || !$this->checkColorValid($value["color"])) {
                    $show_message = true;
                    if (!$erro_messages["message_color_code_invalid"]) {
                        $erro_messages["message_color_code_invalid"] = $this->txt("error_color_code_tag_invalid");
                    }
                }
            }

            if ($show_message) {
                $this->setAlert(implode("<br />", $erro_messages));
                return false;
            }
        }

        return true;
    }

    /**
     * Pregmatch color code if valid html color
     *
     * @param string 	$color
     *
     * @return boolean
     */
    protected function checkColorValid($color)
    {
        return preg_match('/^[a-fA-F0-9]{6}$/i', $color);
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function render($a_mode = "")
    {
        $new_tpl = new ilTemplate("tpl.single_tag_input.html", true, true, $this->plugin_dir);

        $new_tpl->setVariable("NAME_TITLE", $this->txt("tag_name"));
        $new_tpl->setVariable("COLOR_TITLE", $this->txt("tag_color"));
        $new_tpl->setVariable("COLOR_EXAMPEL_TITLE", $this->txt("tag_color_exampel"));
        $new_tpl->setVariable("ALLOCS_TITLE", $this->txt("tag_count_allocs"));
        $new_tpl->setVariable("ACTIONS_TITLE", $this->txt("tag_actions"));

        $values = $this->getValue();
        if (count($values) == 0) {
            $values[] = array();
        }

        $postvar = $this->getPostVar();

        foreach ($values as $key => $value) {
            $new_tpl->touchBlock("row");

            $new_tpl->setCurrentBlock("input_name");
            $new_tpl->setVariable("NAME_ID", $postvar . "[name][$key]");
            $new_tpl->setVariable("PROPERTY_VALUE_NAME", $value["name"]);
            $new_tpl->setVariable("NAME_POST_VAR", $postvar);
            $new_tpl->setVariable("NAME_ROW_NUMBER", $key);
            $new_tpl->setVariable("MAX_LENGTH", self::MAXIMUM_LENGTH_NAME);
            $new_tpl->parseCurrentBlock();

            $new_tpl->setCurrentBlock("input_color");
            $new_tpl->setVariable("COLOR_ID", $postvar . "[color][$key]");
            $new_tpl->setVariable("PROPERTY_VALUE_COLOR", $value["color"]);
            $new_tpl->setVariable("COLOR_POST_VAR", $postvar);
            $new_tpl->setVariable("COLOR_ROW_NUMBER", $key);
            $new_tpl->parseCurrentBlock();

            if ($value["color"] != "" && $value["color"] !== null && $this->checkColorValid($value["color"])) {
                $new_tpl->setCurrentBlock("input_color_exampel");
                $color = $this->df->color('#' . $value["color"]);
                $result_tag = $this->g_f->button()->tag("&nbsp;&nbsp;&nbsp;", "#")->withBackgroundColor($color);
                $new_tpl->setVariable("COLOR", $this->g_renderer->render($result_tag));
                $new_tpl->parseCurrentBlock();
            } elseif ($value["color"] != "" && $value["color"] !== null && !$this->checkColorValid($value["color"])) {
                $new_tpl->setCurrentBlock("input_color_error");
                $new_tpl->setVariable("IMAGE", $this->plugin_dir . "/templates/images/icon_not_ok.svg");
                $new_tpl->parseCurrentBlock();
            } else {
                $new_tpl->touchBlock("input_color_blank");
            }

            $new_tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $new_tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));

            $new_tpl->setVariable("TARGET_ID_POST_VAR", $postvar);
            $new_tpl->setVariable("TARGET_ID_ROW_NUMBER", $key);
            $new_tpl->setVariable("TARGET_ID", $value["id"]);
            $new_tpl->setVariable("TARGET_ID_ID", $postvar . "[id][$key]");

            $new_tpl->setVariable("ALLOCS", $value["allocs"]);

            $new_tpl->setVariable("ID", $postvar . "[name][$key]");
        }

        $this->g_tpl->addJavascript($this->plugin_dir . "/templates/js/ServiceTagMulti.js");

        return $new_tpl->get();
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
