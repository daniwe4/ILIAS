<?php
use CaT\Plugins\CourseClassification\Options\Topic;
use CaT\Plugins\CourseClassification\TableProcessing\TableProcessor;

require_once(__DIR__ . "/../class.ilOptionsGUI.php");

/**
 * Baseclass to configure the topic options for course classification
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilTopicGUI extends ilOptionsGUI
{

    /**
     * @inheritdoc
     */
    protected function getTableGUI()
    {
        return new Topic\ilTopicTableGUI($this, $this->actions, $this->type);
    }

    /**
     * Save new and editet options
     *
     * @return null
     */
    protected function saveOptions()
    {
        $options = $this->getProcessingOptionsFromPost();
        $options = $this->table_processor->process($options, array(TableProcessor::ACTION_SAVE));

        foreach ($options as $key => $option) {
            $id = $option["option"]->getId();
            if ($id != -1) {
                $this->actions->raiseUpdateEventFor($id);
            }
        }

        $this->showOptions($options);
    }

    /**
     * @inheritdoc
     */
    protected function getOptionsFromPost()
    {
        $ret = array();
        $post = $_POST;

        if ($post["caption"] && count($post["caption"]) > 0) {
            foreach ($post["caption"] as $key => $value) {
                $category = null;
                if ($post["category"][$key] && $post["category"][$key] != "") {
                    $category = $this->actions->getCategoryById((int) $post["category"][$key]);
                }

                $ret[$key] = new Topic\Topic((int) $post["hidden_id"][$key], $value, $category);
            }
        }

        return $ret;
    }
}
