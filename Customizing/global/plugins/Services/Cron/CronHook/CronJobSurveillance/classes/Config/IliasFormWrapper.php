<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Wrapper arround ilias forms.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class IliasFormWrapper implements FormWrapper
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    public function __construct(\ilPropertyFormGUI $form)
    {
        $this->form = $form;
    }

    /**
     * @inheritdoc
     */
    public function getHtml()
    {
        return $this->form->getHtml();
    }

    /**
     * @inheritdoc
     */
    public function setInputByArray(array $input)
    {
        $this->form->setValuesByArray($input);
    }

    /**
     * @inheritdoc
     */
    public function checkInput()
    {
        return $this->form->checkInput();
    }
}
