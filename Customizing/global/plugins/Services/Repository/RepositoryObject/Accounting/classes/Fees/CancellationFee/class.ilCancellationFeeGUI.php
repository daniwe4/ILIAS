<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\Fees\CancellationFee;

class ilCancellationFeeGUI
{
    const CMD_SHOW_CANCELLATION_FEE_SETTINGS = "showCancellationFeeSettings";
    const CMD_SAVE_CANCELLATION_FEE_SETTINGS = "saveCancellationFeeSettings";

    const F_CANCELLATION_FEE = "cancellationFee";

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
     * @var CancellationFee\DB
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
        CancellationFee\DB $db,
        Closure $txt
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
            case self::CMD_SHOW_CANCELLATION_FEE_SETTINGS:
                $this->showCancellationFeeSettings();
                break;
            case self::CMD_SAVE_CANCELLATION_FEE_SETTINGS:
                $this->saveCancellationFeeSettings();
                break;
            default:
                throw new \Exception("Unknown command: " . $cmd);
        }
    }

    protected function showCancellationFeeSettings(\ilPropertyFormGUI $form = null)
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
        $form->setTitle($this->txt("cancellation_fee_form_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_CANCELLATION_FEE_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_CANCELLATION_FEE_SETTINGS, $this->txt("cancel"));

        $ni = new \ilNumberInputGUI(
            $this->txt("cancellation_fee_txt"),
            self::F_CANCELLATION_FEE
        );
        $ni->allowDecimals(true);
        $ni->setDecimals(2);
        $ni->setMinValue(0);

        $form->addItem($ni);

        return $form;
    }

    protected function setValues(\ilPropertyFormGUI $form)
    {
        $values = array();

        $cancellation_fee = $this->db->select($this->getObjId());
        $values[self::F_CANCELLATION_FEE] = $cancellation_fee->getCancellationFee();

        $form->setValuesByArray($values);
    }

    protected function saveCancellationFeeSettings()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showCancellationFeeSettings($form);
            return;
        }

        $post = $_POST;
        $cancellation_fee = $this->db->select($this->getObjId());

        $cancellation_fee_value = trim($post[self::F_CANCELLATION_FEE]);
        if ($cancellation_fee_value == "") {
            $cancellation_fee_value = 0;
        } else {
            $cancellation_fee_value = str_replace(",", ".", $cancellation_fee_value);
            $cancellation_fee_value = (float) $cancellation_fee_value;
        }
        $cancellation_fee = $cancellation_fee->withCancellationFee($cancellation_fee_value);

        $this->db->update($cancellation_fee);
        $this->throwEvent();
        \ilUtil::sendSuccess($this->txt("cancellation_fee_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_CANCELLATION_FEE_SETTINGS);
    }

    protected function throwEvent()
    {
        $e = array("xacc" => $this->getObject());
        $this->eventhandler->raise("Plugin/Accounting", "updateCancellationFee", $e);
    }

    public function txt(string $code) : string
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
