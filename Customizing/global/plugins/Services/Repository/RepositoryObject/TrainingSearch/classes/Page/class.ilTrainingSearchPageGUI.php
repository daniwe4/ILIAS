<?php

require_once(__DIR__ . "/class.ilTrainingSearchPage.php");

/**
 * @ilCtrl_Calls ilTrainingSearchPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilTrainingSearchPageGUI: ilPublicUserProfileGUI, ilNoteGUI, ilNewsItemGUI
 * @ilCtrl_Calls ilTrainingSearchPageGUI: ilPropertyFormGUI, ilInternalLinkGUI, ilPageMultiLangGUI
 */
class ilTrainingSearchPageGUI extends ilPageObjectGUI
{
    public function __construct($obj_type, $obj_id, $tpl, $lang)
    {
        global $DIC;
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();

        $style_sheet_id = (int) ilObjStyleSheet::lookupObjectStyle(
            $obj_id
        );

        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($style_sheet_id)
        );

        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();

        if (!ilTrainingSearchPage::_exists(
            $obj_type,
            $obj_id
        )
        ) {
            // doesn't exist -> create new one
            $new_page_object = new ilTrainingSearchPage();
            $new_page_object->setParentId($obj_id);
            $new_page_object->setId($obj_id);
            $new_page_object->setXMLContent("");
            $new_page_object->createFromXML();
        }

        $this->setStyleId(
            \ilObjStyleSheet::getEffectiveContentStyleId(
                $style_sheet_id,
                $obj_type
            )
        );

        $this->setTemplateTargetVar("ADM_CONTENT");
        $this->setFileDownloadLink("");
        $this->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
        $this->setPresentationTitle("");
        $this->setTemplateOutput(false);
        $this->setHeader("");

        parent::__construct($obj_type, $obj_id, 0, false, $lang);
    }

    public function edit()
    {
        $ret = parent::edit();
        $this->tpl->setContent($ret);
    }

    public function preview()
    {
        $ret = parent::preview();
        $this->tpl->setContent($ret);
    }

    public function history()
    {
        $ret = parent::history();
        $this->tpl->setContent($ret);
    }
}
