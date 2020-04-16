<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/Form/classes/class.ilNonEditableValueGUI.php";
require_once __DIR__ . "/Rating/ilRatingGUI.php";
require_once __DIR__ . "/Rating/ilRatingTextualGUI.php";

use \CaT\Plugins\ScaledFeedback;
use CaT\Plugins\ScaledFeedback\Feedback\Rating\Rating;

class ilFeedbackGUI
{
    const CMD_SHOW = "showContent";
    const CMD_FEEDBACK = "showFeedback";
    const CMD_SAVE_FEEDBACK = "saveFeedback";
    const CMD_CONFIRM_FEEDBACK = "confirmFeedback";
    const CMD_CANCEL_CONFIRMATION = "cancelConfirmation";
    const CMD_CANCEL = "cancel";
    const CMD_SHOW_EXTRO = "showExtro";
    const CMD_SHOW_REPEAT = "showRepeat";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ScaledFeedback\Feedback\DB;
     */
    protected $feedback_db;

    /**
     * @var ScaledFeedback\Config\DB;
     */
    protected $config_db;

    /**
     * @var \ilObjScaledFeedback
     */
    protected $object;

    /**
     * @var ScaledFeedback\LPSettings\LPManager
     */
    protected $lp_manager;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilObjUser $user,
        ScaledFeedback\Feedback\DB $feedback_db,
        ScaledFeedback\Config\DB $config_db,
        \ilObjScaledFeedback $object,
        ScaledFeedback\LPSettings\LPManager $lp_manager,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->user = $user;
        $this->feedback_db = $feedback_db;
        $this->config_db = $config_db;
        $this->object = $object;
        $this->lp_manager = $lp_manager;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        if ($this->checkRepeat() && $cmd != self::CMD_SHOW_EXTRO) {
            $cmd = self::CMD_SHOW_REPEAT;
        }

        switch ($cmd) {
            case self::CMD_SHOW:
            case self::CMD_FEEDBACK:
            case self::CMD_CANCEL:
                $this->showFeedback();
                break;
            case self::CMD_SAVE_FEEDBACK:
                $this->saveFeedback();
                break;
            case self::CMD_CONFIRM_FEEDBACK:
                $this->saveConfirmation();
                break;
            case self::CMD_CANCEL_CONFIRMATION:
                $this->cancelConfirmation();
                break;
            case self::CMD_SHOW_EXTRO:
                $this->showExtro();
                break;
            case self::CMD_SHOW_REPEAT:
                $this->showRepeat();
                break;
            default:
                throw new Exception("ilFeedbackGUI:: Unknown command " . $cmd);
        }
    }

    protected function showFeedback(ilPropertyFormGUI $form = null)
    {
        if ($form == null) {
            $form = $this->getForm();
        }
        $this->tpl->setContent($form->getHtml());
    }

    protected function saveConfirmation()
    {
        $form = $this->getForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showFeedback($form);
            return;
        }

        require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");

        $confirmation = new ilConfirmationGUI();
        $confirmation->addHiddenItem('savePost', base64_encode(serialize($_POST)));
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("save_confirmation"));
        $confirmation->setConfirm($this->txt("confirm"), self::CMD_SAVE_FEEDBACK);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL_CONFIRMATION);

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function cancelConfirmation()
    {
        $form = $this->getForm();
        $form->setValuesByPost();
        $this->showFeedback($form);
        return;
    }

    protected function saveFeedback()
    {
        $post = unserialize(base64_decode($_POST['savePost']));

        $parent_obj_id = $this->object->getParentObjId();
        $parent_ref_id = $this->object->getParentRefId();
        $obj_id = (int) $this->object->getId();
        $set_id = $this->object->getSettings()->getSetId();
        $usr_id = (int) $this->user->getId();
        $dimensions = $this->feedback_db->getDimensionsForSetId($set_id);

        foreach ($dimensions as $dim) {
            $feedback = $this->feedback_db->create($obj_id, $set_id, $usr_id, $dim->getDimId());
            $values = $post["dim_rating_" . $dim->getDimId()];
            $rating = (int) $values['rating'];
            $text = $values['textarea'];
            if ($text == null) {
                $text = "";
            }
            $feedback = $feedback
                ->withParentObjId($parent_obj_id)
                ->withParentRefId($parent_ref_id)
                ->withRating($rating)
                ->withCommenttext($text);
            $this->feedback_db->update($feedback);
        }

        if (!is_null($this->object->getParentRefId())) {
            $this->lp_manager->refresh($obj_id);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_EXTRO);
    }

    protected function showExtro()
    {
        $set = $this->getSet();
        $form = $this->getInfoForm($set->getExtrotext());
        $this->tpl->setContent($form->getHtml());
    }

    protected function showRepeat()
    {
        $set = $this->getSet();
        $form = $this->getInfoForm($set->getRepeattext());
        $this->tpl->setContent($form->getHtml());
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $set = $this->getSet();
        $set_id = $set->getSetId();
        $dimensions = $this->feedback_db->getDimensionsForSetId($set_id);

        $ni = new ilNonEditableValueGUI("");
        $ni->setValue($set->getIntrotext());

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("intro"));
        $form->setShowTopButtons(true);
        $form->addCommandButton(self::CMD_CONFIRM_FEEDBACK, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        $form->addItem($ni);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("feedback"));
        $form->addItem($sh);


        foreach ($dimensions as $dim) {
            if ($dim->getEnableComment() && $dim->getOnlyTextualFeedback()) {
                $rt = new ilRatingTextualGUI(
                    $dim->getDisplayedTitle(),
                    $dim->getInfo(),
                    $dim->getDimId(),
                    $this->getPluginDir()
                );
            } else {
                $rating = new Rating();
                $rating = $rating
                    ->withDimTitle($dim->getDisplayedTitle())
                    ->withDimId($dim->getDimId())
                    ->withCaptions(array(
                        $dim->getLabel1(),
                        $dim->getLabel2(),
                        $dim->getLabel3(),
                        $dim->getLabel4(),
                        $dim->getLabel5()
                    ))
                    ->withByline($dim->getInfo())
                    ->withEnableComment($dim->getEnableComment())
                ;
                $rt = new ilRatingGUI(
                    $rating,
                    $this->getPluginDir()
                );
            }

            $form->addItem($rt);
        }
        return $form;
    }

    protected function getInfoForm(string $text) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("feedback"));

        $ni = new ilNonEditableValueGUI("");
        $ni->setValue($text);

        $form->addItem($ni);
        return $form;
    }

    protected function getSet() : ScaledFeedback\Config\Sets\Set
    {
        $set_id = $this->object->getSettings()->getSetId();
        return $this->config_db->selectSetById($set_id);
    }

    protected function checkRepeat() : bool
    {
        $obj_id = (int) $this->object->getId();
        $usr_id = (int) $this->user->getId();
        return $this->feedback_db->checkRepeat($obj_id, $usr_id);
    }

    protected function getPluginDir() : string
    {
        return $this->object->getPluginPath();
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
