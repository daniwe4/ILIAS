<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

/**
 * Represents the interface to ilias form elements.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-trainings.de>
 */
interface FormBuilder
{
    /**
     * Get the form.
     *
     * @param 	string 	$name 		The name of the form.
     * @param 	string 	$action 	The performed action on submit.
     * @param 	string 	$target 	Decides where to open the target.
     * 								Possible values are:
     * 									_self (default)
     * 									_blank
     * 									_parent
     * 									_top
     * @return 	FormWrapper
     */
    public function getForm(string $name, string $action, string $target = "_self") : FormWrapper;

    /**
     * Add a ilCJSConfigHeaderGUI.
     *
     * @param 	string 	$header_select 		Header for the name column.
     * @param 	string 	$header_number 		Header for the number column.
     * @return 	void
     */
    public function addCJSConfigHeaderGUI(string $header_name, string $header_number) : void;

    /**
     * Add a ilCJSConfigItemGUI.
     *
     * @param 	array 				$options_select_box		Options for the selectbox.
     * @param 	ConfigurationForm 	$configuration_form
     * @return 	void
     */
    public function addCJSConfigItemGUI(
        array $options_select_box,
        ConfigurationForm $configuration_form
    ) : void;

    /**
     * Add a button to the form.
     *
     * @param 	string 	$name 		The name of the button. Used for html-dom.
     * @param 	string 	$text 		The displayed text on the button.
     * @param 	string 	$action 	Specifies the action of the button.
     * @return 	void
     */
    public function addButton(string $name, string $text, string $action) : void;
}
