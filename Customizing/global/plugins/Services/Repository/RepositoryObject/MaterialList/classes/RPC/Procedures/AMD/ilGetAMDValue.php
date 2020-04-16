<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\AMD;

use CaT\Plugins\MaterialList\RPC;

require_once("Services/Membership/classes/class.ilParticipants.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");

/**
 * Procedure to get amd value of course
 */
class ilGetAMDValue extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "xlsx_member_count";

    /**
     * Set id of field
     *
     * @param int 	$field_id
     */
    public function setFieldId($field_id)
    {
        assert('is_int($field_id)');
        $this->field_id = $field_id;
    }

    /**
     * Return the title of crs
     *
     * @return RPC\FunctionResult
     */
    public function run()
    {
        $amd_values = \ilAdvancedMDValues::getInstancesForObjectId($this->crs->getId(), "crs");
        foreach ($amd_values as $key => $amd_value) {
            $amd_value->read();
            foreach ($amd_value->getDefinitions() as $key => $definition) {
                if ($definition->getFieldId() == $this->field_id) {
                    switch ($definition->getType()) {
                        case \ilAdvancedMDFieldDefinition::TYPE_SELECT:
                            return new RPC\FunctionResult(
                                $definition->getTitle(),
                                $definition->getADT()->getSelection()
                            );
                            break;
                        case \ilAdvancedMDFieldDefinition::TYPE_TEXT:
                            return new RPC\FunctionResult(
                                $definition->getTitle(),
                                $definition->getADT()->getText()
                            );
                            break;
                        case \ilAdvancedMDFieldDefinition::TYPE_DATE:
                            $date = "";
                            if ($definition->getADT()->getDate() !== null) {
                                $date = $definition->getADT()->getDate()->get(IL_CAL_FKT_DATE, "d.m.Y");
                            }
                            return new RPC\FunctionResult(
                                $definition->getTitle(),
                                $date
                            );
                            break;
                        case \ilAdvancedMDFieldDefinition::TYPE_DATETIME:
                            $date = "";
                            if ($definition->getADT()->getDate() !== null) {
                                $date = $definition->getADT()->getDate()->get(IL_CAL_FKT_DATE, "d.m.Y H:i:s");
                            }
                            return new RPC\FunctionResult(
                                $definition->getTitle(),
                                $date
                            );
                            break;
                        case \ilAdvancedMDFieldDefinition::TYPE_INTEGER:
                        case \ilAdvancedMDFieldDefinition::TYPE_FLOAT:
                            return new RPC\FunctionResult(
                                $definition->getTitle(),
                                $definition->getADT()->getNumber()
                            );
                            break;
                        case \ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI:
                            $selected = "";
                            if ($definition->getADT()->getSelections()) {
                                $selected = implode(", ", $definition->getADT()->getSelections());
                            }
                            return new RPC\FunctionResult(
                                $definition->getTitle(),
                                $selected
                            );
                            break;
                        case \ilAdvancedMDFieldDefinition::TYPE_LOCATION:
                            return new RPC\FunctionResult(
                                $definition->getTitle(),
                                implode(
                                    ", ",
                                    array($this->txt("xlsx_lat") . ": " . $definition->getADT()->getLatitude(),
                                                                  $this->txt("xlsx_long") . ": " . $definition->getADT()->getLongitude()
                                                            )
                                                )
                            );
                            break;
                    }
                }
            }
        }
    }
}
