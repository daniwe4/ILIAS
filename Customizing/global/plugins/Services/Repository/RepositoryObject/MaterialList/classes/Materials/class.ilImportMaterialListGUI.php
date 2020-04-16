<?php

use \CaT\Plugins\MaterialList\ilPluginActions;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

class ilImportMaterialListGUI
{
    const CMD_SHOW_FORM = "showForm";
    const CMD_CANCEL = "cancelUpload";
    const CMD_UPLOAD = "uploadList";

    /**
     * @var \ilCtr
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var \ilMaterialsGUI
     */
    protected $parent_gui;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(\ilMaterialsGUI $parent_gui, ilPluginActions $plugin_actions, \Closure $txt)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $this->plugin_actions = $plugin_actions;
        $this->txt = $txt;
        $this->parent_gui = $parent_gui;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_FORM);
        switch ($cmd) {
            case self::CMD_SHOW_FORM:
                $this->showForm();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            case self::CMD_UPLOAD:
                $this->uploadList();
                break;
        }
    }

    /**
     * Shows the upload form
     *
     * @param \ilPropertyFormGUI | null
     *
     * @return null
     */
    protected function showForm(\ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Init the upload form
     *
     * @return \ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("material_upload_form_title"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_UPLOAD, $this->txt("material_upload"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("material_cancel_upload"));

        $fi = new \ilFileInputGUI($this->txt("material_file"), ilPluginActions::F_FILE_INPUT);
        $fi->setSuffixes(array("xlsx"));
        $form->addItem($fi);

        $ci = new \ilCheckboxInputGUI($this->txt("delete_existing"), ilPluginActions::F_DELETE_EXISTING);
        $ci->setInfo($this->txt("delete_existing_info"));
        $ci->setValue(1);
        $form->addItem($ci);

        return $form;
    }

    /**
     * Read the uploaded list and save entries
     *
     * @return null
     */
    protected function uploadList()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showForm($form);
            return;
        }


        $post = $_POST;
        $file = $post[ilPluginActions::F_FILE_INPUT];
        if ($file["size"] > 0) {
            if ($post[ilPluginActions::F_DELETE_EXISTING] == "1") {
                $this->plugin_actions->deleteAllMaterials();
            }
            $this->readFile($file);
        } else {
            \ilUtil::sendInfo($this->txt("no_materials_imported"), true);
        }

        $this->g_ctrl->redirect($this->parent_gui);
    }

    /**
     * Read the uploaded file
     *
     * @param string[]
     *
     * @return null
     */
    protected function readFile(array $file)
    {
        $file_path = $file["tmp_name"];
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($file_path);
        $show_skip_message = false;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if (!$this->plugin_actions->articleNumberKnown((string) $row[0])) {
                    $this->plugin_actions->createNewMaterial((string) $row[0], (string) $row[1]);
                } else {
                    $show_skip_message = true;
                }
            }
        }

        if ($show_skip_message) {
            \ilUtil::sendInfo($this->txt("material_import_skiped"), true);
        }

        $reader->close();
    }

    /**
     * Cancel upload
     *
     * @return null
     */
    protected function cancel()
    {
        $this->g_ctrl->redirect($this->parent_gui);
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        assert('is_string($code)');

        $txt = $this->txt;

        return $txt($code);
    }
}
