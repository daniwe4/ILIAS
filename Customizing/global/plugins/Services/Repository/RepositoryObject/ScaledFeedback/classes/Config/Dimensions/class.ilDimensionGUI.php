<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/Form/classes/class.ilFormSectionHeaderGUI.php";
require_once "Services/Form/classes/class.ilTextInputGUI.php";

use \CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;
use \CaT\Plugins\ScaledFeedback\Config\DB;

/**
 * Class ilDimensionGUI.
 * GUI for creating new Dimensions.
 */
class ilDimensionGUI
{
    const F_DIM_ID = "dim_id";
    const F_TITLE = "title";
    const F_DISPLAYED_TITLE = "displayed_title";
    const F_INFO = "info";
    const F_IS_LOCKED = "is_locked";
    const F_ENABLE_COMMENT = "enable_comment";
    const F_ONLY_TEXTUAL_FEEDBACK = "only_textual_feedback";
    const F_LABEL1 = "label1";
    const F_LABEL2 = "label2";
    const F_LABEL3 = "label3";
    const F_LABEL4 = "label4";
    const F_LABEL5 = "label5";

    const CMD_ADD_DIMENSION = "addDimension";
    const CMD_EDIT_DIMENSION = "editDimension";
    const CMD_CANCEL = "cancel";
    const CMD_SAVE_DIMENSION = "saveDimension";
    const CMD_UPDATE_DIMENSION = "updateDimension";
    const CMD_SAVE_EDIT_DIMENSION = "saveEditDimension";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var string
     */
    protected $parent_gui_link;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        DB $db,
        string $parent_gui_link,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->parent_gui_link = $parent_gui_link;
        $this->txt = $txt;
    }

    /**
     * Delegate commands.
     *
     * @throws 	Exception
     * @return 	void
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_ADD_DIMENSION);

        switch ($cmd) {
            case self::CMD_EDIT_DIMENSION:
                $this->editDimension();
                break;
            case self::CMD_ADD_DIMENSION:
                $this->addDimension();
                break;
            case self::CMD_SAVE_DIMENSION:
                $this->saveDimension();
                break;
            case self::CMD_UPDATE_DIMENSION:
                $this->updateDimension();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    /**
     * Shows form to add a new dimension
     */
    protected function addDimension(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->getForm();
        }

        $form->addCommandButton(self::CMD_SAVE_DIMENSION, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        $this->showContent($form);
    }

    /**
     * Shows form to edit a dimension
     */
    protected function editDimension(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $dimension = $this->db->selectDimensionById($this->validateIdFromGet());
            $form = $this->getForm($dimension->getIsUsed());
            $this->fillForm($form, $dimension);
        }

        $form->addCommandButton(self::CMD_UPDATE_DIMENSION, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        $this->showContent($form);
    }

    /**
     * Show editing form for dimensions.
     */
    protected function showContent(ilPropertyFormGUI $form)
    {
        $this->tpl->setContent($form->getHtml());
    }

    protected function saveDimension()
    {
        $form = $this->getForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->addDimension($form);
            return;
        }

        $post = $_POST;
        $title = trim($post[self::F_TITLE]);
        $displayed_title = trim($post[self::F_DISPLAYED_TITLE]);
        if ($this->db->isDimensionTitleInUse($title)) {
            $form->setValuesByPost();
            $this->addDimension($form);
            \ilUtil::sendFailure($this->txt("double_title"));
            return;
        }

        $dimension = $this->db->createDimension($title, $displayed_title);
        $dimension = $this->getDimensionWithPostValues($dimension, $post);

        $this->db->updateDimension($dimension);
        \ilUtil::sendSuccess($this->txt("create_successful"), true);
        $this->ctrl->redirectToURL($this->parent_gui_link);
    }

    /**
     * Update dimension.
     */
    protected function updateDimension()
    {
        $post = $_POST;
        $dim_id = $this->validateIdFromGet();
        $dimension = $this->db->selectDimensionById($dim_id);

        $form = $this->getForm($dimension->getIsUsed());
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showContent($form);
            return;
        }

        $title = trim($post[self::F_TITLE]);
        $title_in_use = $this->db->isDimensionTitleInUse($title, false);

        if ($dimension->getTitle() !== $title && $title_in_use) {
            $form->setValuesByPost();
            $this->editDimension($form);
            \ilUtil::sendFailure($this->txt("double_title"));
            return;
        }

        if (!(bool) $post[self::F_IS_LOCKED]
            && $dimension->getIsLocked()
            && $title_in_use
        ) {
            $post[self::F_IS_LOCKED] = 1;
            $form->setValuesByArray($post);
            $this->editDimension($form);
            \ilUtil::sendFailure($this->txt("cant_unlock_title_in_use"));
            return;
        }

        $dimension = $this->getDimensionWithPostValues($dimension, $post);
        $this->db->updateDimension($dimension);
        \ilUtil::sendSuccess($this->txt("edit_successful"), true);
        $this->ctrl->redirectToURL($this->parent_gui_link);
    }

    protected function cancel()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilDimensionsGUI"),
            ilDimensionsGUI::CMD_SHOW_DIMENSIONS,
            '',
            false,
            false
        );
        $this->ctrl->redirectToURL($link);
    }

    /**
     * Updates current or new dimension with values from post
     *
     * @param string[] 	$post
     *
     * @return Dimension
     */
    protected function getDimensionWithPostValues(Dimension $dimension, array $post) : Dimension
    {
        if ($dimension->getIsUsed()) {
            return $dimension->withIsLocked((bool) $post[self::F_IS_LOCKED]);
        }

        return $dimension
            ->withTitle($post[self::F_TITLE])
            ->withDisplayedTitle($post[self::F_DISPLAYED_TITLE])
            ->withInfo($post[self::F_INFO])
            ->withLabel1($post[self::F_LABEL1])
            ->withLabel2($post[self::F_LABEL2])
            ->withLabel3($post[self::F_LABEL3])
            ->withLabel4($post[self::F_LABEL4])
            ->withLabel5($post[self::F_LABEL5])
            ->withEnableComment((bool) $post[self::F_ENABLE_COMMENT])
            ->withIsLocked((bool) $post[self::F_IS_LOCKED])
            ->withOnlyTextualFeedback((bool) $post[self::F_ONLY_TEXTUAL_FEEDBACK])
            ->withIsLocked((bool) $post[self::F_IS_LOCKED]);
    }

    protected function getForm(bool $disable = false) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("settings"));
        $form->setShowTopButtons(true);

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $ti->setValidationRegexp("/.{3,}/");
        $ti->setValidationFailureMessage($this->txt("lt_three_chars"));
        $ti->setDisabled($disable);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("displayed_title"), self::F_DISPLAYED_TITLE);
        $ti->setRequired(true);
        $ti->setValidationRegexp("/.{3,}/");
        $ti->setValidationFailureMessage($this->txt("lt_three_chars"));
        $ti->setDisabled($disable);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->txt("info"), self::F_INFO);
        $ta->setDisabled($disable);
        $form->addItem($ta);

        $ci = new ilCheckBoxInputGUI($this->txt("is_locked"), self::F_IS_LOCKED);
        $ci->setInfo($this->txt("is_locked_info"));
        $form->addItem($ci);

        $ci = new ilCheckBoxInputGUI($this->txt("enable_comment"), self::F_ENABLE_COMMENT);
        $ci->setInfo($this->txt("enable_comment_info"));
        $ci->setDisabled($disable);
        $cbi = new ilCheckboxInputGUI("", self::F_ONLY_TEXTUAL_FEEDBACK);
        $cbi->setInfo($this->txt("only_textual_feedback"));
        $cbi->setDisabled($disable);
        $ci->addSubItem($cbi);
        $form->addItem($ci);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("scale_points"));
        $form->addItem($sh);

        $ti = new ilTextInputGUI($this->txt("label1"), self::F_LABEL1);
        $ti->setDisabled($disable);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("label2"), self::F_LABEL2);
        $ti->setDisabled($disable);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("label3"), self::F_LABEL3);
        $ti->setDisabled($disable);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("label4"), self::F_LABEL4);
        $ti->setDisabled($disable);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt("label5"), self::F_LABEL5);
        $ti->setDisabled($disable);
        $form->addItem($ti);

        $hi = new ilHiddenInputGUI(self::F_DIM_ID);
        $form->addItem($hi);

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form, Dimension $dimension)
    {
        $arr = array(
            self::F_DIM_ID => $dimension->getDimId(),
            self::F_TITLE => $dimension->getTitle(),
            self::F_DISPLAYED_TITLE => $dimension->getDisplayedTitle(),
            self::F_INFO => $dimension->getInfo(),
            self::F_LABEL1 => $dimension->getLabel1(),
            self::F_LABEL2 => $dimension->getLabel2(),
            self::F_LABEL3 => $dimension->getLabel3(),
            self::F_LABEL4 => $dimension->getLabel4(),
            self::F_LABEL5 => $dimension->getLabel5(),
            self::F_ENABLE_COMMENT => $dimension->getEnableComment(),
            self::F_ONLY_TEXTUAL_FEEDBACK => $dimension->getOnlyTextualFeedback(),
            self::F_IS_LOCKED => $dimension->getIsLocked()
        );

        $form->setValuesByArray($arr);
    }

    protected function validateIdFromGet() : int
    {
        if (isset($_GET['id']) && $this->db->isValidDimId((int) $_GET['id'])) {
            return (int) $_GET['id'];
        } elseif (isset($_POST['dim_id']) && $_POST['dim_id'] != "") {
            return (int) $_POST['dim_id'];
        }
        return -1;
    }

    /**
     * Translate code to lang value
     */
    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
