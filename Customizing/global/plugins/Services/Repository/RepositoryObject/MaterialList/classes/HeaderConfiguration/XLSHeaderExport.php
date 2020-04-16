<?php
/* Copyright (c) 2017 Daniel Weise <daniel.weise@concepts-and-training.de>, Extended GPL, see LICENSE */

namespace CaT\Plugins\MaterialList\HeaderConfiguration;

/**
 * Manages the XLSHeaderOptions
 */
class XLSHeaderExport
{
    /**
     * @var CaT\Plugins\MaterialList\RPC\ProcedureLoader
     */
    protected $procedure_loader;

    /**
     * Constructor of the class XLSHeaderExport
     *
     * @param CaT\Plugins\MaterialList\RPC\ProcedureLoader 	$procedur_loader
     */
    public function __construct(\CaT\Plugins\MaterialList\RPC\ProcedureLoader $procedure_loader)
    {
        $this->procedure_loader = $procedure_loader;
    }

    /**
     * Get values for header in excel export
     *
     * @param \ilObjCourse 								$crs
     *
     * @return string[][]
     */
    public function getHeaderValuesForExport($crs, $header_settings)
    {
        $ret = array();

        foreach ($header_settings as $header_setting) {
            switch ($header_setting->getType()) {
                case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_STANDARD:
                    $ret = array_merge($ret, $this->getStandardProcedureValues($header_setting, $crs));
                    break;
                case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_AMD:
                    $ret = array_merge($ret, $this->getAMDProcedureValues($header_setting, $crs));
                    break;
                case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_FUNCTION:
                    $ret = array_merge($ret, $this->getCustomProcedureValues($header_setting, $crs));
                    break;
                default:
                    throw new \Exception(__METHOD__ . " unkown field type: " . $field_type);
            }
        }
        $this->addTutorData($crs, $ret);
        $this->addActualDate($ret);
        return $ret;
    }

    /**
     * Load the procedure
     *
     * @param string 			$module
     * @param string 			$procedure_name
     * @param \ilObjCourse 		$crs
     *
     * @return ilFunctionBase
     */
    protected function loadProcedure($module, $procedure_name, $crs)
    {
        return $this->procedure_loader->loadProcedure($module, $procedure_name, $crs);
    }

    /**
     * Get value array for standard procedures
     *
     * @param MaterialList\HeaderConfiguration\ConfigurationEntry;
     *
     * @return string[]
     */
    protected function getStandardProcedureValues(\CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry $header_entry, $crs)
    {
        $procedure = $this->loadProcedure("Course", $header_entry->getSourceForValue(), $crs);
        $value = $procedure->run();

        return $this->makeArrayFlat(array($value->getTitle(), $value->getValue()));
    }

    /**
     * Get value array for amd procedures
     *
     * @param MaterialList\HeaderConfiguration\ConfigurationEntry;
     *
     * @return string[]
     */
    protected function getAMDProcedureValues(\CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry $header_entry, $crs)
    {
        $procedure = $this->loadProcedure("AMD", "getAMDValue", $crs);
        $procedure->setFieldId((int) $header_entry->getSourceForValue());
        $value = $procedure->run();

        return $this->makeArrayFlat(array($value->getTitle(), $value->getValue()));
    }


    /**
     * Get value array for custom procedures
     *
     * @param MaterialList\HeaderConfiguration\ConfigurationEntry;
     *
     * @return string[]
     */
    protected function getCustomProcedureValues(\CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry $header_entry, $crs)
    {
        $procedure = $this->loadProcedure("Custom", $header_entry->getSourceForValue(), $crs);
        $value = $procedure->run();

        return $this->makeArrayFlat(array($value->getTitle(), $value->getValue()));
    }

    /**
     * Flat an array so it is an one dimensional array
     *
     * @param array 		$array
     *
     * @return array
     */
    protected function makeArrayFlat(array $array)
    {
        assert(count($array) == 2);
        if (is_array($array[1])) {
            $title = $array[0];
            $ret = [];
            foreach ($array[1] as $val) {
                $ret[] = [$title, $val];
                $title = "";
            }
            return $ret;
        }
        assert(is_string($array[0]));
        return [$array];
    }


    /**
     * Add a tutor data for requests
     *
     * @param int 		$crs_id
     * @param string[]
     *
     * @return void
     */
    protected function addTutorData($crs, &$ret)
    {
        $name = $this->nullToString($crs->getContactName());
        $phone = $this->nullToString($crs->getContactPhone());
        $mail = $this->nullToString($crs->getContactEmail());

        if ($name !== "") {
            $ret[] = array("Bei Rückfragen", $name);
        }

        if ($phone !== "") {
            $ret[] = array("", $phone);
        }

        if ($mail !== "") {
            $ret[] = array("", $mail);
        }
    }

    /**
     * Transform null to empty string
     *
     * @param string | null 		$value
     *
     * @return string
     */
    protected function nullToString($value)
    {
        if ($value === null) {
            return "";
        }
        return $value;
    }
    /**
     * Add actual date
     *
     * @param string[]
     *
     * @return void
     */
    protected function addActualDate(&$ret)
    {
        $ret[] = array("Datum der Erstellung", date("d.m.Y"));
    }

    /**
     * Get current configuration entries
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry[]
     */
    public function getConfigurationEntries()
    {
        return $this->header_configuration_db->selectAll();
    }
}
