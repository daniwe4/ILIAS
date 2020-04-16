<?php

namespace CaT\Plugins\MaterialList\HeaderConfiguration\TypeForms;

/**
 * Baseclass for all type forms.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
abstract class ilTypeForm
{
    const F_FIELD = "field";

    /**
     * @var \ilFormPropertyGUI
     */
    protected $form;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var string[]
     */
    protected $options;

    /**
     * Init form
     *
     * @return null
     */
    abstract public function initForm();

    /**
     * Add a command button
     *
     * @param string 	$cmd
     * @param string 	$label
     *
     * @return null;
     */
    public function addCommandButton($cmd, $label)
    {
        $this->form->addCommandButton($cmd, $label);
    }

    /**
     * Add form action
     *
     * @param string 	$form_action
     *
     * @return null
     */
    public function addFormAction($form_action)
    {
        $this->form->setFormAction($form_action);
    }

    /**
     * Check the form input values
     *
     * @return bool
     */
    public function checkInput()
    {
        return $this->form->checkInput();
    }

    /**
     * Get the html of Form
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->form->getHtml();
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

    /**
     * Set the form values from post array
     *
     * @return null
     */
    public function setValuesByPost()
    {
        $this->form->setValuesByPost();
    }

    /**
     * Get value
     *
     * @return string | int
     */
    public function getValue()
    {
        return $_POST[self::F_FIELD];
    }
}
