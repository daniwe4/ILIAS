<?php
namespace CaT\Plugins\Accounting\Config\VatRate;

/**
 * Object class for VatRate
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class VatRate
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
     * Constructor of the class VatRate
     *
     * @param int 		$id
     * @param string 	$value 		default ""
     * @param string 	$label 		default ""
     *
     */
    public function __construct($id, $value = "", $label = "", $active = false)
    {
        $this->setId($id);
        $this->setValue($value);
        $this->setLabel($label);
        $this->setActive($active);
    }

    /**
     * Get the id from VatRate
     *
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Get the value from VatRate
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the label from VatRate
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the active state
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
     * @return VatRate 		$clone
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
     * @return VatRate 		$clone
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
     * @return VatRate 		$clone
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
     * @return VatRate 		$clone
     */
    public function withActive($value)
    {
        $clone = clone $this;
        $clone->setActive($value);
        return $clone;
    }
}
