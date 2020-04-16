<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\UserSettings;

/**
 * GUI for users' settings.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class CalSettingsTableGUI extends \ilTable2GUI
{
    const F_USE = 'f_use';
    const F_HIDE_DETAILS = 'f_hide';
    const F_STORAGE_ID = 'f_storage_id';
    const F_CALCAT_ID = 'f_calcat_id';
    const F_USR_ID = 'f_usr_id';

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var int
     */
    protected $counter;

    public function __construct(
        \ilTrainerOperationsCalSettingsGUI $parent_gui
    ) {
        $this->setId("tep_cal_settings");
        parent::__construct($parent_gui);
        $this->parent_gui = $parent_gui;
        $this->configureTable();
        $this->counter = 0;
    }

    protected function configureTable()
    {
        $this->setExternalSorting(true);

        $this->setShowRowsSelector(true);
        $this->addColumn($this->txt("cal_settings_type")); //private/genral
        $this->addColumn($this->txt("cal_settings_title"));
        $this->addColumn($this->txt("cal_settings_use_here"));
        $this->addColumn($this->txt("cal_settings_hide_details"));
        $this->addColumn(""); //$this->txt("actions"));
    }

    public function fillRow($a_set)
    {
        $this->counter++;
        $setting = $a_set;

        $this->tpl->setVariable("TYPE", $this->txt($setting->getType()));
        $this->tpl->setVariable("TITLE", $setting->getTitle());

        $hidden_fields = [
            'counter' => $this->counter,
            $this->parent_gui::F_STORAGE_ID => $setting->getStorageId(),
            $this->parent_gui::F_CALCAT_ID => $setting->getCalCatId(),
            $this->parent_gui::F_USR_ID => $setting->getUserId()
        ];
        $hidden_value = htmlentities(json_encode($hidden_fields));
        $this->tpl->setVariable('FIELD_NAME_OBJ_INFO', $this->parent_gui::F_OBJ_INFO);
        $this->tpl->setVariable('HIDDEN_FIELD_VALUE', $hidden_value);

        $this->tpl->setVariable("FIELD_NAME_USE", $this->parent_gui::F_USE);
        $this->tpl->setVariable("USE_VALUE", $this->counter);
        $this->tpl->setVariable("FIELD_NAME_HIDE_DETAILS", $this->parent_gui::F_HIDE_DETAILS);
        $this->tpl->setVariable("HIDE_DETAILS_VALUE", $this->counter);

        if ($setting->getShow()) {
            $this->tpl->touchBlock('use');
        }
        if ($setting->getHideDetails()) {
            $this->tpl->touchBlock('hide_details');
        }

        $actions = $this->parent_gui->getActions($setting->getCalCatId());
        $this->tpl->setVariable("ACTIONS", $actions);
        ;
    }


    protected function txt($code)
    {
        return $this->parent_gui->txt($code);
    }
}
