<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/Form/classes/class.ilTextInputGUI.php";
require_once "Services/Form/classes/class.ilNonEditableValueGUI.php";

use \CaT\Plugins\ScaledFeedback;

class ilEvaluationGUI extends TMSTableParentGUI
{
    const CMD_SHOW_EVALUATION = "showEvaluation";
    const CMD_SHOW_NOT_ENOUGH_FEEDBACKS = "showNotEnoughFeedbacks";

    const TABLE_ID = "evaluation";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ScaledFeedback\Feedback\DB
     */
    protected $feedback_db;

    /**
     * @var ScaledFeedback\Config\DB
     */
    protected $config_db;

    /**
     * @var \ilObjScaledFeedback
     */
    protected $object;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ScaledFeedback\Feedback\DB $feedback_db,
        ScaledFeedback\Config\DB $config_db,
        \ilObjScaledFeedback $object,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->feedback_db = $feedback_db;
        $this->config_db = $config_db;
        $this->object = $object;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_EVALUATION:
                $this->showEvaluation();
                break;
            case self::CMD_SHOW_NOT_ENOUGH_FEEDBACKS:
                $this->showNotEnoughFeedbacks();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
                break;
        }
    }

    protected function showEvaluation(ilPropertyFormGUI $form = null)
    {
        $data = $this->getProcessData();
        if ($form == null) {
            $form = $this->getForm($data);
        }
        $this->renderEvaluationTable($form, $data);
    }

    public function showNotEnoughFeedbacks()
    {
        $obj = $this->object;
        $obj_id = (int) $obj->getId();
        $set_id = (int) $obj->getSettings()->getSetId();
        $amount_of_feedbacks = $this->feedback_db->getAmountOfFeedbacks($obj_id, $set_id);
        $min_submissions = $this->config_db->getMinSubmissionsBySetId($set_id);

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("evaluation"));

        $out = sprintf(
            $this->txt("not_enough_feedbacks"),
            $min_submissions,
            $amount_of_feedbacks
        );

        $ni = new ilNonEditableValueGUI("");
        $ni->setValue($out);

        $form->addItem($ni);

        $this->tpl->setContent($form->getHtml());
    }

    protected function renderEvaluationTable(ilPropertyFormGUI $form, array $data)
    {
        $table = $this->getTMSTableGUI();

        $table->setDescription($this->txt("count_answers") . " " . $this->getParticipants($data));
        $table->setData($data);
        $this->tpl->setContent($table->getHtml() . $form->getHtml());
    }

    protected function getForm(array $data) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        foreach ($data as $dat) {
            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($dat['title']);
            $form->addItem($sh);

            $comments = array_filter($dat["comment"], function ($c) {
                return $c !== "";
            });
            $text = $this->txt("no_comments");

            if (count($comments) > 0) {
                $tpl = new ilTemplate(
                    "tpl.evaluation_text.html",
                    true,
                    true,
                    $this->object->getPluginPath()
                );

                foreach ($comments as $comment) {
                    $tpl->setCurrentBlock("value");
                    $tpl->setVariable("VALUE", $comment);
                    $tpl->parseCurrentBlock();
                }
                $text = $tpl->get();
            }

            $ni = new ilNonEditableValueGUI("", "", true);
            $ni->setValue($text);
            $form->addItem($ni);
        }
        return $form;
    }

    protected function getProcessData() : array
    {
        $obj = $this->object;
        $obj_id = (int) $obj->getId();
        $set_id = (int) $obj->getSettings()->getSetId();
        $feedbacks = $this->feedback_db->selectByIds($obj_id, $set_id);
        $min_submissions = $this->config_db->getMinSubmissionsBySetId($set_id);

        if ($feedbacks == null) {
            $this->ctrl->redirect($this, self::CMD_SHOW_NOT_ENOUGH_FEEDBACKS);
        }

        foreach ($feedbacks as $feedback) {
            $dim_id = $feedback->getDimId();
            $set_id = $feedback->getSetId();

            $arr[$dim_id]['title'] = $this->feedback_db->getDimensionDisplayedTitleById($dim_id);
            $arr[$dim_id]['participants'] += 1;
            $arr[$dim_id]['points'] += $feedback->getRating();
            $arr[$dim_id]['order'] = $this->config_db->getDimensionOrdernumber($set_id, $dim_id);
            $arr[$dim_id]['comment'][$feedback->getUsrId()] = $feedback->getCommenttext();
        }

        if ($this->getParticipants($arr) < $min_submissions) {
            $this->ctrl->redirect($this, self::CMD_SHOW_NOT_ENOUGH_FEEDBACKS);
        }

        return $this->sortFeedbacks($arr);
    }

    protected function sortFeedbacks(array $feedbacks) : array
    {
        uasort($feedbacks, function ($a, $b) {
            if ($a['order'] < $b['order']) {
                return -1;
            }
            if ($a['order'] > $b['order']) {
                return 1;
            }
            return 0;
        });

        return $feedbacks;
    }

    protected function getParticipants(array $data) : int
    {
        foreach ($data as $dat) {
            return $dat['participants'];
        }
    }

    protected function getTMSTableGUI() : ilTMSTableGUI
    {
        $table = parent::getTMSTableGUI();

        $table->setEnableTitle(true);
        $table->setTitle($this->txt("evaluation"));
        $table->setTopCommands(false);
        $table->setEnableHeader(true);
        $table->setRowTemplate(
            "tpl.table_evaluation_row.html",
            $this->object->getPluginPath()
        );
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setExternalSorting(true);
        $table->setEnableAllCommand(true);
        $table->setShowRowsSelector(false);

        $table->addColumn($this->txt("dimensions"));
        $table->addColumn($this->txt("average"));

        return $table;
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, array $set) {
            $tpl = $table->getTemplate();

            $avg = round($set['points'] / $set['participants'], 2);
            $avg = number_format($avg, 2);

            $tpl->setVariable("DIMENSION", $set['title']);
            $tpl->setVariable("AVERAGE", $avg);
        };
    }

    protected function tableCommand()
    {
        return self::CMD_SHOW_EVALUATION;
    }

    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
