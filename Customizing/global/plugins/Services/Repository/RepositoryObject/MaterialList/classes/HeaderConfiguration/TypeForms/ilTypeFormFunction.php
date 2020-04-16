<?php

namespace CaT\Plugins\MaterialList\HeaderConfiguration\TypeForms;

use \CaT\Plugins\MaterialList\ilPluginActions;
use \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry;

class ilTypeFormFunction extends ilTypeForm
{
    public function __construct(\Closure $txt, array $options)
    {
        $this->txt = $txt;
        $this->options = $options;

        $this->initForm();
    }

    /**
     * @inheritdoc
     */
    public function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new \ilPropertyFormGUI();
        $this->form->setTitle(sprintf($this->txt("configuration_entry_set_value_title"), $this->txt("type_function")));

        $si = new \ilSelectInputGUI($this->txt("source_for_value"), self::F_FIELD);
        $si->setOptions($this->options);
        $this->form->addItem($si);

        $hi = new \ilHiddenInputGUI(ilPluginActions::F_NEW_ENTRY);
        $hi->setValue(ConfigurationEntry::TYPE_FUNCTION);
        $this->form->addItem($hi);
    }
}
