<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\WBDManagement\Config\UserDefinedFields\WBDManagementUDF;
use CaT\Plugins\WBDManagement\Config\UserDefinedFields\WBDManagementUDFStorage;

class ilWBDManagementUDFConfigGUI extends TMSTableParentGUI
{
    const CMD_SHOW = "show";
    const CMD_SAVE = "save";
    const CMD_CANCEL = "cancel";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var WBDManagementUDFStorage
     */
    protected $storage;

    /**
     * @var string
     */
    protected $plugin_path;

    /**
     * @var array
     */
    protected $fields;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilToolbarGUI $toolbar,
        Closure $txt,
        WBDManagementUDFStorage $storage,
        string $plugin_path,
        array $fields
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->txt = $txt;
        $this->storage = $storage;
        $this->plugin_path = $plugin_path;
        $this->fields = $fields;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW:
                $this->show();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function show()
    {
        $this->renderTable($this->storage->readAll($this->fields));
    }

    protected function cancel()
    {
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function save()
    {
        $post = $_POST;

        $udfs = array();
        foreach ($this->fields as $field) {
            if (array_key_exists($field, $post)) {
                $udf_id = (int) $post[$field];

                if (is_null($udf_id) || $udf_id <= 0) {
                    ilUtil::sendFailure(sprintf($this->txt("udf_id_cant_be_null"), $udf_id), true);
                    $this->ctrl->redirect($this, self::CMD_SHOW);
                }

                $udfs[] = new WBDManagementUDF($field, $udf_id);
            }
        }

        $this->storage->save($udfs);

        ilUtil::sendSuccess($this->txt("save_success"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    /**
     * @param WBDManagementUDF[] $data
     */
    protected function renderTable(array $data)
    {
        $table = $this->getTMSTableGUI();
        $this->configureTable($table);
        $table->setData($data);
        $this->tpl->setContent(
            $table->getHtml()
        );
    }

    protected function configureTable(ilTMSTableGUI $table)
    {
        $table->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW));
        $table->setTitle($this->txt("udf_fields_configuration"));
        $table->setExternalSegmentation(false);
        $table->setShowRowsSelector(false);
        $table->setRowTemplate("tpl.user_defined_fields_row.html", $this->plugin_path);
        $table->addColumn($this->txt("udf_field_name"));
        $table->addColumn($this->txt("udf_field_value"));
        $table->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $table->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, WBDManagementUDF $data) {
            $table->getTemplate()->setVariable("POST_VAR", "check");
            $table->getTemplate()->setVariable("ID", $data->getUdfId());
            $table->getTemplate()->setVariable("HIDDEN_KEY", $data->getName());
            $table->getTemplate()->setVariable("HIDDEN_VALUE", $data->getUdfId());

            $ne = new ilNonEditableValueGUI();
            $ne->setValue($this->txt($data->getName()));
            $table->getTemplate()->setVariable("UDF_FIELD_NAME", $ne->render());

            $ni = new ilNumberInputGUI("", $data->getName());
            $ni->setValue($data->getUdfId());
            $table->getTemplate()->setVariable("UDF_FIELD_VALUE", $ni->render());
        };
    }

    protected function tableCommand()
    {
        return self::CMD_SHOW;
    }

    protected function tableId()
    {
        return get_class($this);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
