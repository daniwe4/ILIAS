<?php

declare(strict_types = 1);

use CaT\Plugins\WBDCommunicator\Config\UDF;

class ilWBDUDFGUI
{
    const CMD_SHOW = "show";
    const CMD_SAVE = "save";

    const KEY_GUTBERATEN_ID = "gutberaten_id";
    const KEY_WBD_STATUS_ID = "wbd_status";

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
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        UDF\DB $db,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->txt = $txt;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW:
                $this->show();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    public function show(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->buildForm();
            $this->setValues($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function setValues(\ilPropertyFormGUI $form)
    {
        $gutberaten_id_udf = "";
        $gutberaten_id = $this->db->getUDFFieldIdForWBDID();
        if (!is_null($gutberaten_id)) {
            $gutberaten_id_udf = $gutberaten_id->getFieldId();
        }

        $wbd_status_udf = "";
        $wbd_status = $this->db->getUDFFieldIdForStatus();
        if (!is_null($wbd_status)) {
            $wbd_status_udf = $wbd_status->getFieldId();
        }

        $values = [
            self::KEY_GUTBERATEN_ID => $gutberaten_id_udf,
            self::KEY_WBD_STATUS_ID => $wbd_status_udf
        ];

        $form->setValuesByArray($values);
    }

    protected function buildForm()
    {
        require_once "Services/Form/classes/class.ilPropertyFormGUI.php";

        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt('udf_configuration'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $udf_wbd_id = new ilNumberInputGUI($this->txt('form_udf_wbd_id'), self::KEY_GUTBERATEN_ID);
        $udf_wbd_id->setMinValue(1);
        $udf_wbd_id->allowDecimals(false);
        $udf_wbd_id->setRequired(true);
        $form->addItem($udf_wbd_id);

        $udf_announce_id = new ilNumberInputGUI($this->txt('form_udf_wbd_status'), self::KEY_WBD_STATUS_ID);
        $udf_announce_id->setMinValue(1);
        $udf_announce_id->allowDecimals(false);
        $udf_announce_id->setRequired(true);
        $form->addItem($udf_announce_id);

        $form->addCommandButton(self::CMD_SAVE, $this->txt('save'));

        return $form;
    }

    public function save()
    {
        $form = $this->buildForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->show($form);
            return;
        }

        $post = $_POST;
        $udf_wbd_id = (int) $post[self::KEY_GUTBERATEN_ID];
        $this->db->saveUDFFieldIdForWBDID($udf_wbd_id);

        $udf_wbd_status_id = (int) $post[self::KEY_WBD_STATUS_ID];
        $this->db->saveUDFFieldIdForStatus($udf_wbd_status_id);

        ilUtil::sendSuccess($this->txt("wbd_fields_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
