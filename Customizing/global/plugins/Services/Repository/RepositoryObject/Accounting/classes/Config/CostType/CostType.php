<?php
namespace CaT\Plugins\Accounting\Config\CostType;

/**
 * Object class for CostType
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class CostType
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var int
     */
    protected $active;

    /**
     * Constructor of the class CostType
     *
     * @param int 		$id
     * @param string 	$value 		default ""
     * @param string 	$label 		default ""
     * @param boolean 	$active 	default false
     */
    public function __construct($id, $value = "", $label = "", $active = false)
    {
        $this->setId($id);
        $this->setValue($value);
        $this->setLabel($label);
        $this->setActive($active);
    }

    /**
     * Get the id from CostType
     *
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Get the value from CostType
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the label from CostType
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the active state from CostType
     *
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the id
     *
     * @param int 	$value
     */
    protected function setId(int $value)
    {
        $this->id = $value;
    }

    /**
     * Set the value
     *
     * @param string 	$value
     */
    protected function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * Set the label
     *
     * @param string 	$value
     */
    protected function setLabel(string $value)
    {
        $this->label = $value;
    }

    /**
     * Set the active status
     *
     * @param bool 		$value
     */
    protected function setActive(bool $value)
    {
        $this->active = $value;
    }

    /**
     * Get cloned object with new id (immutable)
     *
     * @param int 			$value
     * @return CostType 	$clone
     */
    public function withId($value)
    {
        $clone = clone $this;
        $clone->setId($value);
        return $clone;
    }

    /**
     * Get cloned object with new value (immutable)
     *
     * @param string 		$value
     * @return CostType 	$clone
     */
    public function withValue($value)
    {
        $clone = clone $this;
        $clone->setValue($value);
        return $clone;
    }

    /**
     * Get cloned object with new label (immutable)
     *
     * @param string 		$value
     * @return CostType 	$clone
     */
    public function withLabel($value)
    {
        $clone = clone $this;
        $clone->setLabel($value);
        return $clone;
    }

    /**
     * Get cloned object with new active value (immutable)
     *
     * @param int 			$value
     * @return CostType 	$clone
     */
    public function withActive($value)
    {
        $clone = clone $this;
        $clone->setActive($value);
        return $clone;
    }
}
