<?php declare(strict_types=1);

use CaT\Plugins\ParticipationsImport\Mappings\ConfigStorage;
use CaT\Plugins\ParticipationsImport\Mappings\Mapping;

abstract class ilPIStatusMappingConfiguratorGUI extends TMSTableParentGUI
{
    const COL_ORDER = 'order';
    const COL_ERROR = 'error';
    const COL_STATUS_EXTERN = 'status_extern';
    const COL_STATUS_ILIAS = 'status_ilias';

    const CMD_SHOW = 'show_status_mapping';
    const CMD_SAVE = 'save_status_mapping';
    const CMD_DELETE_ENTRIES = 'delete_status_mapping';

    const TABLE_ID = 'participation_import_config_status_mapping_table';


    public function __construct(
        \ilParticipationsImportPlugin $plugin,
        \ilCtrl $ctrl,
        \ilTemplate $tpl,
        ConfigStorage $cs
    ) {
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->cs = $cs;
    }

    public function withParent(ilPIMappingsConfiguratorGUI $gui)
    {
        $other = clone $this;
        $other->parent = $gui;
        return $other;
    }



    abstract protected function storeMapping(Mapping $mapping);

    abstract protected function getEmptyMapping() : Mapping;

    abstract protected function loadMapping() : Mapping;

    abstract protected function getTitle() : string;

    protected function filledTable(array $data = [])
    {
        $table = $this->getTMSTableGUI();
        if (count($data) === 0) {
            $order = 0;
            foreach ($this->loadMapping() as $extern => $ilias) {
                $row = [];
                $row[self::COL_STATUS_EXTERN] = $extern;
                $row[self::COL_STATUS_ILIAS] = $ilias;
                $row[self::COL_ORDER] = ++$order;
                $row[self::COL_ERROR] = '';
                $data[] = $row;
            }
            $row = [];
            $row[self::COL_STATUS_EXTERN] = '';
            $row[self::COL_STATUS_ILIAS] = '';
            $row[self::COL_ORDER] = 'is_new';
            $row[self::COL_ERROR] = '';
            $data[] = $row;
        }

        $table->setData($data);
        return $table;
    }

    protected function deleteDigest(string $status_extern, string $status_ilias)
    {
        return $status_extern . '_' . $status_ilias;
    }



    /**
     * Get the basic command table has to use
     *
     * @return string
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW;
    }

    /**
     * Get the id of table
     *
     * @return string
     */
    protected function tableId()
    {
        return self::TABLE_ID;
    }

    /**
     * Execute current ctrl commad for this GUI
     *
     * @return void
     */
    public function executeCommand()
    {
        $this->cmd = $this->ctrl->getCmd();
        switch ($this->cmd) {
            case self::CMD_SHOW:
                $this->show();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            case self::CMD_DELETE_ENTRIES:
                $this->deleteEntries();
                break;
            default:
                throw new Exception('invalid command ' . $this->cmd);
        }
        return true;
    }

    protected function show(array $data = [])
    {
        $tbl = $this->filledTable($data);
        $this->tpl->setContent(
            $tbl->getHTML()
        );
    }


    protected function deleteEntries()
    {
        $to_delete = $_POST['delete_id'];
        if (!is_array($to_delete)) {
            $to_delete = [];
        }
        $sm = $this->getEmptyMapping();
        foreach ($this->loadMapping() as $status_extern => $status_ilias) {
            if (in_array($this->deleteDigest($status_extern, $status_ilias), $to_delete)) {
                continue;
            }
            $sm->addRelation($status_extern, $status_ilias);
        }
        $this->storeMapping($sm);
        $this->show();
    }

    protected function getTMSTableGUI()
    {
        $table = parent::getTMSTableGUI();
        $table->setTitle($this->getTitle());
        $table->addMulticommand(self::CMD_DELETE_ENTRIES, $this->plugin->txt('delete'));
        $table->setRowTemplate(
            "tpl.status_relation_config_row.html",
            $this->plugin->getDirectory()
        );
        $table->addCommandButton(self::CMD_SAVE, $this->plugin->txt(self::CMD_SAVE));
        $table->addColumn("", "", "1", true);
        $table->addColumn($this->plugin->txt('status_extern'));
        $table->addColumn($this->plugin->txt('status_ilias'));
        $table->setFormAction($this->ctrl->getFormAction($this));
        return $table;
    }

    protected function fillRow()
    {
        $selection_pre = $this->loadMapping()->properValues();
        $selection = [];
        foreach ($selection_pre as $status_ilias) {
            $selection[$status_ilias] = $this->plugin->txt($status_ilias);
        }
        asort($selection);
        return function ($table, $data) use ($selection) {
            $order = $data[self::COL_ORDER];
            $template = $table->getTemplate();
            if ($order !== 'is_new') {
                $template->setCurrentBlock("cb");
                $template->setVariable(
                    'DELETE_DIGEST',
                    $this->deleteDigest(
                        (string) $data[self::COL_STATUS_EXTERN],
                        (string) $data[self::COL_STATUS_ILIAS]
                    )
                );
                $template->parseCurrentBlock();
            }
            $status_extern = new \ilTextInputGUI("", 'status_extern[' . $order . ']');
            $status_extern->setValue($data[self::COL_STATUS_EXTERN]);
            $template->setVariable('STATUS_EXTERN', $status_extern->render());

            $status_ilias = new \ilSelectInputGUI("", 'status_ilias[' . $order . ']');
            $status_ilias->setOptions($selection);
            $status_ilias->setValue($data[self::COL_STATUS_ILIAS]);

            $table->getTemplate()->setVariable('STATUS_ILIAS', $status_ilias->render());
            if (trim((string) $data[self::COL_ERROR]) !== '') {
                $template->setCurrentBlock("alert");
                $template->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
                $template->setVariable("TXT_ALERT", $data[self::COL_ERROR]);
                $template->parseCurrentBlock();
            }
        };
    }

    protected function save()
    {
        $status_extern = $_POST['status_extern'];
        $status_ilias = $_POST['status_ilias'];
        $save_data = [];
        $show_data = [];
        $saving_status_extern = [];
        $saving_status_ilias = [];
        $errors = 0;
        foreach ($status_extern as $index => $status_ex) {
            if ($index === 'is_new') {
                continue; // make sure new is at the end
            }
            $row = $this->extractRowFromPost(
                $status_extern,
                $status_ilias,
                $saving_status_extern,
                $saving_status_ilias,
                $errors,
                $index
            );
            $show_data[] = $row;
            $save_data[] = $row;
        }
        if (trim((string) $status_extern['is_new']) !== '') {
            $row = $this->extractRowFromPost(
                $status_extern,
                $status_ilias,
                $saving_status_extern,
                $saving_status_ilias,
                $errors,
                'is_new'
            );
            $show_data[] = $row;
            $save_data[] = $row;
        } else {
            $row[self::COL_ORDER] = 'is_new';
            $row[self::COL_STATUS_EXTERN] = '';
            $row[self::COL_STATUS_ILIAS] = '';
            $row[self::COL_ERROR] = '';
            $show_data[] = $row;
        }
        if ($errors > 0) {
            $this->show($show_data);
        } else {
            $this->saveData($save_data);
            $this->show();
        }
    }


    protected function saveData(array $data)
    {
        $sm = $this->getEmptyMapping();
        foreach ($data as $values) {
            $sm->addRelation(
                $values[self::COL_STATUS_EXTERN],
                $values[self::COL_STATUS_ILIAS]
            );
        }
        $this->storeMapping($sm);
    }


    protected function extractRowFromPost(
        array $post_status_extern,
        array $post_status_ilias,
        array &$saving_status_extern,
        array &$saving_status_ilias,
        &$errors,
        $index
    ) {
        $row = [];
        $error = '';
        $status_ilias = $post_status_ilias[$index];
        $status_extern = trim((string) $post_status_extern[$index]);

        if ($status_extern === '') {
            $error .= $this->plugin->txt('no_status_title');
            $errors++;
        }
        if (in_array($status_extern, $saving_status_extern)) {
            $error .= $this->plugin->txt('overwrite_status');
            $errors++;
        }
        $saving_status_extern[] = $status_extern;
        $saving_status_ilias[] = $status_ilias;
        $row[self::COL_ORDER] = $index;
        $row[self::COL_STATUS_EXTERN] = $status_extern;
        $row[self::COL_STATUS_ILIAS] = $status_ilias;
        $row[self::COL_ERROR] = $error;
        return $row;
    }
}
