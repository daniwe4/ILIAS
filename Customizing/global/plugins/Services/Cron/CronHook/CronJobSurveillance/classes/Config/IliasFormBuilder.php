<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once(__DIR__ . "/class.ilCJSConfigItemGUI.php");
require_once(__DIR__ . "/class.ilCJSConfigHeaderGUI.php");

/**
 * Build a config form with ilias elements.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-trining.de>
 */
class IliasFormBuilder implements FormBuilder
{
    /**
     * @var \ilCJSConfigHeaderGUI
     */
    protected $header;

    /**
     * @var \ilCJSConfigItemGUI
     */
    protected $item;

    /**
     * @var array
     */
    protected $buttons = [];

    public function __construct()
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
    }

    /**
     * @inheritdoc
     */
    public function getForm(string $name, string $action, string $target = "_self") : FormWrapper
    {
        $form = new \ilPropertyFormGUI();

        $form->setTitle($name);
        $form->setFormAction($this->g_ctrl->getFormActionByClass($action));
        $form->setTarget($target);
        $form->addItem($this->header);
        $form->addItem($this->item);
        foreach ($this->buttons as $button) {
            $form->addCommandButton($button['action'], $button['text'], $button['name']);
        }

        return new IliasFormWrapper($form);
    }

    /**
     * @inheritdoc
     */
    public function addCJSConfigHeaderGUI(
        string $header_name,
        string $header_number
    ) : void {
        $header = new \ilCJSConfigHeaderGUI("", "");

        $header = $header
            ->withHeaderName($header_name)
            ->withHeaderNumber($header_number)
        ;

        $this->header = $header;
    }

    /**
     * @inheritdoc
     */
    public function addCJSConfigItemGUI(
        array $options_select_box,
        ConfigurationForm $configuration_form
    ) : void {
        $item = new \ilCJSConfigItemGUI("", "");

        $item = $item
            ->withOptions($options_select_box)
            ->withConfigurationForm($configuration_form)
        ;

        $this->item = $item;
    }

    /**
     * @inheritdoc
     */
    public function addButton(string $name, string $text, string $action)  :void
    {
        $this->buttons[] = array(
            'name' => $name,
            'text' => $text,
            'action' => $action
        );
    }
}
