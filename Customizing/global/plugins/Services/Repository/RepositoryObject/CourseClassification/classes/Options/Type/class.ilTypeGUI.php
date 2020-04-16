<?php
use CaT\Plugins\CourseClassification\Options;
use CaT\Plugins\CourseClassification\TableProcessing\TableProcessor;

require_once(__DIR__ . "/../class.ilOptionsGUI.php");

/**
 * Configure class for types in course classification
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilTypeGUI extends ilOptionsGUI
{
    /**
     * Save new and editet options
     *
     * @return null
     */
    protected function saveOptions()
    {
        $options = $this->getProcessingOptionsFromPost();
        $options_saved = $this->table_processor->process($options, array(TableProcessor::ACTION_SAVE));

        foreach ($options as $key => $option) {
            $id = $option["option"]->getId();
            if ($id != -1) {
                $this->actions->raiseUpdateEventFor($id);
            }
        }
        $this->showOptions($options_saved);
    }
}
