<?php
require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
require_once('Services/Form/interfaces/interface.ilMultiValuesItem.php');

use CaT\Plugins\CronJobSurveillance\Config\ConfigurationForm;

/**
 * Holds the gui for a config item.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilCJSConfigItemGUI extends ilSubEnabledFormPropertyGUI implements ilMultiValuesItem
{
    const F_SELECTBOX_NAME = "job_id";
    const F_NUMBERBOX_NAME = "tolerance";
    const MIN_TOLERANCE = 1;

    /**
     * @var string
     */
    protected $name_selectbox;

    /**
     * @var array 	Holds options for selectbox.
     */
    protected $options;

    /**
     * @var string
     */
    protected $name_numberbox;

    /**
     * @var ConfigurationForm
     */
    protected $configuration_form;

    /**
     * Constructor of the class ilCJSConfigItemGUI.
     *
     * @param 	string 	$title 		The title of the subform.
     * @param 	string 	$postvar 	The post var.
     */
    public function __construct($title = "", $postvar = "")
    {
        parent::__construct($title, $postvar);
        
        $this->options = array();
        $this->name_numberbox = "";
        $this->setMulti(true);
    }

    /**
     * Render the GUI.
     */
    public function render()
    {
        $tpl = new ilTemplate(__DIR__ . "/../../templates/default/tpl.cjs_config_item.html", true, true);
        $selected = "";
        $tolerance = "";

        $cf = $this->getConfigurationForm();
        $mv = $this->getMultiValues();
        $job_settings = $cf->genrateJobSettingsArray($mv);

        if (is_array($job_settings) && !empty($job_settings)) {
            $first = array_shift($job_settings);
            $selected = $first->getJobId();
            $tolerance = $first->getTolerance();

            $this->setHiddenFields($tpl, $job_settings);
        }

        $tpl->setCurrentBlock("gui");
        $tpl->setVariable("SELECT_NAME", self::F_SELECTBOX_NAME . "[]");
        $tpl->setVariable("OPTIONS", $this->getOptionsHtml($selected));

        $numberbox = $this->getNumberbox();
        $numberbox->setValue($tolerance);
        $tpl->setVariable("NUMBER_BOX", $numberbox->render());

        if ($this->getMulti()) {
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Set a hidden field foreach cron job to observe.
     *
     * @param 	ilGlobalTemplateInterface 	$tpl
     * @param 	array 		$multi_values
     * @return 	void
     */
    protected function setHiddenFields(ilTemplate $tpl, array $job_settings)
    {
        $id = 0;
        foreach ($job_settings as $job_setting) {
            $hidden_element = $this->getHiddenTag(
                "ilMultiValues~" . $id,
                $job_setting->getJobId() . "~" .
                $job_setting->getTolerance()
            );
            $tpl->setCurrentBlock("hidden");
            $tpl->setVariable("HIDDEN_ELEMENT", $hidden_element);
            $tpl->parseCurrentBlock();
            $id++;
        }
    }

    /**
     * Get all options for a selectbox as html.
     *
     * @param 	string 	$selected 	Name of the selected item.
     * @return 	string
     */
    protected function getOptionsHtml($selected)
    {
        $tpl = new ilTemplate(__DIR__ . "/../../templates/default/tpl.cjs_config_options.html", true, true);

        $tpl->setCurrentBlock("select_options");
        $tpl->setVariable("OPTION_VALUE", "-1");
        $tpl->setVariable("OPTION_LABEL", "Bitte wählen");
        $tpl->parseCurrentBlock();

        foreach ($this->getOptions() as $key => $option) {
            $tpl->setCurrentBlock("select_options");
            $tpl->setVariable("OPTION_VALUE", $key);
            $tpl->setVariable("OPTION_LABEL", $option);
            if ($key == $selected) {
                $tpl->setVariable("OPTION_SELECTED", "selected=\"selected\"");
            }
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Get hidden tag (used for disabled properties)
     */
    public function getHiddenTag($post_var, $value)
    {
        return '<input type="hidden" name="' . $post_var . '" id="' . $post_var . '" value="' . ilUtil::prepareFormOutput($value) . '" />';
    }

    /**
     * Insert property html
     *
     * @return	int	Size
     */
    public function insert($tpl)
    {
        $html = $this->render();

        $tpl->setCurrentBlock("prop_generic");
        $tpl->setVariable("PROP_GENERIC", $html);
        $tpl->parseCurrentBlock();
    }

    /**
     * Get an element from post array by name.
     *
     * @param 	string 	$name
     */
    public function getElementByPostVar($name)
    {
        assert('is_string($name)');

        $post = $_POST;
        return $post[$name];
    }

    /**
     * Get options
     *
     * @return 	array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options with $value
     *
     * @param 	array		$value
     * @return 	this
     */
    public function withOptions(array $value)
    {
        $clone = clone $this;
        $clone->options = $value;
        return $clone;
    }

    /**
     * Get configuration_form
     *
     * @return ConfigurationForm
     */
    public function getConfigurationForm()
    {
        return $this->configuration_form;
    }

    /**
     * Set configuration_form with $value
     *
     * @param 	ConfigurationForm		$value
     * @return 	$this
     */
    public function withConfigurationForm(ConfigurationForm $value)
    {
        $clone = clone $this;
        $clone->configuration_form = $value;
        return $clone;
    }

    /**
     * Get multi_icons
     *
     * @return 	bool
     */
    public function getMultiIcons()
    {
        return $this->multi_icons;
    }

    /**
     * Get a number input gui.
     *
     * @return 	string
     */
    public function getNumberbox()
    {
        require_once("Services/Form/classes/class.ilNumberInputGUI.php");

        return new ilNumberInputGUI('', self::F_NUMBERBOX_NAME . "[]");
    }

    /**
     * Check input.
     *
     * @return bool
     */
    public function checkInput()
    {
        $selects = $this->getElementByPostVar(self::F_SELECTBOX_NAME);
        $numbers = $this->getElementByPostVar(self::F_NUMBERBOX_NAME);

        if ($this->checkSelects($selects) || $this->checkTolerance($numbers)) {
            return false;
        }

        return true;
    }

    /**
     * Check for correct select values.
     *
     * @param array
     * @return bool
     */
    protected function checkSelects(array $selects)
    {
        if (count(array_unique($selects)) < count($selects)) {
            return true;
        }

        foreach ($selects as $select) {
            if ($select == '-1') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for correct numberbox input.
     *
     * @param array 	$numbers
     * @return bool
     */
    protected function checkTolerance(array $numbers)
    {
        foreach ($numbers as $number) {
            if (!is_numeric($number) || (int) $number < self::MIN_TOLERANCE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set multi values.
     *
     * @param array $values
     */
    public function setMultiValues(array $values)
    {
        $this->multi_values = $values;
    }

    /**
     * Set the form values by JobSetting array.
     *
     * @param 	array 	$job_settings
     * @return 	void
     */
    public function setValueByArray(array $job_settings)
    {
        $result = array();

        foreach ($job_settings as $key => $job_setting) {
            $result["job_id"][$key] = $job_setting->getJobId();
            $result["tolerance"][$key] = $job_setting->getTolerance();
        }

        $this->setMultiValues($result);
    }
}
