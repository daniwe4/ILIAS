<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\Fees\Fee;

class ilFeeGUI
{
    const CMD_SHOW_FEE_SETTINGS = "showFeeSettings";
    const CMD_SAVE_FEE_SETTINGS = "saveFeeSettings";
    const F_FEE_VALUE = "f_fee_value";

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    /**
     * @var ilAppEventHandler
     */
    protected $eventhandler;
    /**
     * @var Fee\DB
     */
    protected $db;
    /**
     * @var Closure
     */
    protected $txt;
    /**
     * @var int
     */
    protected $obj_id;
    /**
     * @var int
     */
    protected $ref_id;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilAppEventHandler $eventhandler,
        Fee\DB $db,
        \Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->eventhandler = $eventhandler;
        $this->db = $db;
        $this->txt = $txt;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_FEE_SETTINGS:
                $this->showFeeSettings();
                break;
            case self::CMD_SAVE_FEE_SETTINGS:
                $this->saveFeeSettings();
                break;
            default:
                throw new \Exception("Unknown command: " . $cmd);
        }
    }

    protected function showFeeSettings(\ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->setValues($form);
        }

        $this->tpl->setContent($form->getHtml());
    }

    protected function initForm() : \ilPropertyFormGUI
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("fee_form_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_FEE_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_FEE_SETTINGS, $this->txt("cancel"));

        $ni = new \ilNumberInputGUI($this->txt("fee_value"), self::F_FEE_VALUE);
        $ni->allowDecimals(true);
        $ni->setDecimals(2);
        $ni->setMinValue(0);

        $form->addItem($ni);

        return $form;
    }

    protected function setValues(\ilPropertyFormGUI $form)
    {
        $values = array();

        $fee = $this->db->select($this->getObjId());
        $values[self::F_FEE_VALUE] = $fee->getFee();

        $form->setValuesByArray($values);
    }

    protected function saveFeeSettings()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showFeeSettings($form);
            return;
        }

        $post = $_POST;
        $fee = $this->db->select($this->getObjId());

        $fee_value = trim($post[self::F_FEE_VALUE]);
        if ($fee_value == "") {
            $fee_value = null;
        } else {
            $fee_value = str_replace(",", ".", $fee_value);
            $fee_value = (float) $fee_value;
        }
        $fee = $fee->withFee($fee_value);

        $this->db->update($fee);
        $this->throwEvent();

        \ilUtil::sendSuccess($this->txt("fee_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_FEE_SETTINGS);
    }

    protected function throwEvent()
    {
        $e = array("xacc" => $this->getObject());
        $this->eventhandler->raise("Plugin/Accounting", "updateFee", $e);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    protected function getObjId() : int
    {
        if (is_null($this->obj_id)) {
            $get = $_GET;
            $ref_id = $get["ref_id"];
            $this->obj_id = (int) ilObject::_lookupObjId($ref_id);
        }
        return $this->obj_id;
    }

    protected function getRefId()
    {
        if (is_null($this->ref_id)) {
            $get = $_GET;
            $this->ref_id = $get["ref_id"];
        }
        return $this->ref_id;
    }

    protected function getObject()
    {
        return ilObjectFactory::getInstanceByRefId($this->getRefId());
    }
}
