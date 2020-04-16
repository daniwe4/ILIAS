<?php
/* Copyright (c) 2017 Daniel Weise <daniel.weise@concepts-and-training.de>, Extended GPL, see LICENSE */

namespace CaT\Plugins\MaterialList\HeaderConfiguration;

/**
 * Manages the XLSHeaderOptions
 */
class XLSHeaderOptions
{
    /**
     * Constructor of class XLSHeaderOptions
     *
     * @param \CaT\Plugins\MaterialList\RPC\FolderReader 	$folder_reader
     */
    public function __construct(\CaT\Plugins\MaterialList\RPC\FolderReader $folder_reader)
    {
        $this->folder_reader = $folder_reader;
    }

    /**
     * Get the value options for standard type
     *
     * @return array<string, string>
     */
    public function getStandardOptions()
    {
        $entries = $this->folder_reader->getCourseFunctionOptions(__DIR__ . "/../RPC/Procedures/Course");

        if (!$this->isPluginActive("venues")) {
            $this->filterEntries($entries);
        }
        return $entries;
    }

    /**
     * Get the value options for amd type
     *
     * @return array<string, string>
     */
    public function getAMDOptions()
    {
        require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
        $definitions = \ilAdvancedMDFieldDefinition::getInstancesByObjType("crs");
        $ret = array();
        foreach ($definitions as $definition) {
            $ret[$definition->getFieldId()] = $definition->getTitle();
        }
        return $ret;
    }

    /**
     * Get the value options for function type
     *
     * @return array<string, string>
     */
    public function getFunctionOptions()
    {
        return $this->folder_reader->getCustomFunctionOptions(__DIR__ . "/../RPC/Procedures/Custom");
    }

    /**
     * Filter Venues
     */
    protected function filterEntries(array &$entries)
    {
        if (array_key_exists("getVenue", $entries)) {
            $entries["getVenueUnset"] = $entries["getVenue"];
            unset($entries["getVenue"]);
        }
    }

    /**
     * Check if a specific plugin is active
     *
     * @param string 		$name
     *
     * @return bool
     */
    public function isPluginActive($name)
    {
        assert('is_string($name)');

        require_once("./Services/Component/classes/class.ilPluginAdmin.php");
        return \ilPluginAdmin::isPluginActive($name);
    }

    /**
     * Get options for source for value depending on type
     *
     * @param int 	$type
     *
     * @return array<string,string>
     *
     * @throws \Exception
     */
    public function getSourceForValueOptionsByType($type)
    {
        switch ($type) {
            case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_STANDARD:
                return $this->getStandardOptions();
            case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_AMD:
                return $this->getAMDOptions();
            case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_FUNCTION:
                return $this->getFunctionOptions();
            default:
                throw new \Exception(__METHOD__ . " unkown field type: " . $type);
        }
    }
}
