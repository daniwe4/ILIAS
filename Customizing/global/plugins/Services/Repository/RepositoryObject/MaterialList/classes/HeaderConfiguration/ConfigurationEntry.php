<?php

namespace CaT\Plugins\MaterialList\HeaderConfiguration;

/**
 * Single configuration entry for header in xls export.
 * Keeps type of entry and the source the value for print can be found
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ConfigurationEntry
{
    const TYPE_STANDARD = "standard";
    const TYPE_AMD = "amd";
    const TYPE_FUNCTION = "function";

    public static $type_options = array(self::TYPE_STANDARD, self::TYPE_AMD, self::TYPE_FUNCTION);

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $source_for_value;

    /**
     * @param int 		$id
     * @param string 	$type
     * @param string 	$source_for_value
     */
    public function __construct($id, $type = self::TYPE_STANDARD, $source_for_value = "")
    {
        assert('is_int($id)');
        assert('is_string($type)');
        assert('in_array($type, array(self::TYPE_STANDARD, self::TYPE_AMD, self::TYPE_FUNCTION))');
        assert('is_string($source_for_value)');

        $this->id = $id;
        $this->type = $type;
        $this->source_for_value = $source_for_value;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the type of the entry
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the source where value can be found
     *
     * @return string
     */
    public function getSourceForValue()
    {
        return $this->source_for_value;
    }

    /**
     * Set the source where value can be found
     *
     * @param string
     *
     * @return ConfigurationEntry
     */
    public function withSourceForValue($source_for_value)
    {
        assert('is_string($source_for_value)');
        $clone = clone $this;
        $clone->source_for_value = $source_for_value;
        return $clone;
    }
}
