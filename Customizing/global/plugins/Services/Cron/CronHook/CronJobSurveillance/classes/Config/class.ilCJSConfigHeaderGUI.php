<?php
require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");

/**
 * Holds the gui for a config item.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilCJSConfigHeaderGUI extends ilSubEnabledFormPropertyGUI
{
    /**
     * @var string
     */
    protected $header_name;

    /**
     * @var string
     */
    protected $header_number;

    /**
     * Constructor of the class ilCJSConfigItemGUI.
     *
     * @param 	string	$title 		The title of the subform.
     * @param 	string	$postvar 	The post var.
     */
    public function __construct($title = "", $postvar = "")
    {
        parent::__construct($title, $postvar);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return "config_header";
    }

    /**
     * Render the GUI.
     */
    public function render()
    {
        $header = new ilTemplate(__DIR__ . "/../../templates/default/tpl.column_header.html", true, true);
        $header->setVariable("HEADER_SELECT", $this->getHeaderName());
        $header->setVariable("HEADER_NUMBER", $this->getHeaderNumber());
        return $header->get();
    }

    /**
     * Insert property html
     *
     * @return	int	Size
     */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get header_name
     *
     * @return 	string
     */
    public function getHeaderName()
    {
        return $this->header_name;
    }

    /**
     * Set header_name with $value
     *
     * @param 	string		$value
     * @return 	$this
     */
    public function withHeaderName(string $value)
    {
        $clone = clone $this;
        $clone->header_name = $value;
        return $clone;
    }

    /**
     * Get header_number
     *
     * @return 	string
     */
    public function getHeaderNumber()
    {
        return $this->header_number;
    }

    /**
     * Set header_number with $value
     *
     * @param 	string		$value
     * @return 	$this
     */
    public function withHeaderNumber(string $value)
    {
        $clone = clone $this;
        $clone->header_number = $value;
        return $clone;
    }

    /**
     * Dummy method to avoid errors during broadcast calls.
     *
     * @param 	array 	$dummy
     * @return 	void
     */
    public function setValueByArray($dummy)
    {
    }

    /**
     * Dummy method to avoid errors during broadcast calls.
     *
     * @return bool
     */
    public function checkInput()
    {
        return true;
    }
}
