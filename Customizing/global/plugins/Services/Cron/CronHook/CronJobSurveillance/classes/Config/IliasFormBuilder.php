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
     * @var ilCJSConfigHeaderGUI
     */
    protected $header;

    /**
     * @var ilCJSConfigItemGUI
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
    public function getForm($name, $action, $target = "_self")
    {
        assert('is_string($name)');
        assert('is_string($action)');
        assert('is_string($target)');

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
        $header_name,
        $header_number
    ) {
        assert('is_string($header_name)');
        assert('is_string($header_number)');

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
    ) {
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
    public function addButton($name, $text, $action)
    {
        assert('is_string($name)');
        assert('is_string($text)');
        assert('is_string($action)');

        $this->buttons[] = array(
            'name' => $name,
            'text' => $text,
            'action' => $action
        );
    }
}
