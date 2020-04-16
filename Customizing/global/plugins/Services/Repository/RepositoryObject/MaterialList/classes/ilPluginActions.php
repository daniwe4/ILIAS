<?php

namespace CaT\Plugins\MaterialList;

/**
 * ilPluginActions just for actions within the plugin Configuration
 */
class ilPluginActions
{
    const F_IDS = "ids";
    const F_IDS_TO_DEL = "to_delete_ids";
    const F_TYPES = "types";
    const F_SOURCES_FOR_VALUE = "source_for_value";
    const F_NEW_ENTRY = "new_entry";

    const F_NEW_MATERIAL_LINE = "new_material_line";
    const F_CURRENT_MATERIAL_HIDDEN_IDS = "current_material_hidden_ids";
    const F_CURRENT_MATERIAL_IDS = "current_material_ids";
    const F_CURRENT_MATERIAL_ARTICLE_NUMBERS = "current_material_article_numbers";
    const F_OLD_MATERIAL_ARTICLE_NUMBERS = "old_material_article_numbers";
    const F_CURRENT_MATERIAL_TITLES = "current_material_titles";
    const F_FILE_INPUT = "file";
    const F_MATERIAL_BEHAVIOR = "material_behavior";
    const F_DELETE_EXISTING = "delete_existing";

    const MATERIAL_MODE_FREE = "free";
    const MATERIAL_MODE_AUTO_COMPLETE = "auto_complete";
    const MATERIAL_MODE_MIXED = "mixed";

    public static $behavior = array(self::MATERIAL_MODE_FREE, self::MATERIAL_MODE_AUTO_COMPLETE, self::MATERIAL_MODE_MIXED);

    const MATERIAL_BEHAVIOR_SETTING = "material_behavior";

    /**
     * @var ilMaterialListPlugin
     */
    protected $plugin_object;

    /**
     * @var \CaT\Plugins\MaterialList\HeaderConfiguration\DB
     */
    protected $header_configuration_db;

    /**
     * @var \CaT\Plugins\MaterialList\Materials\DB
     */
    protected $materials_db;

    /**
     * @var \ilSetting
     */
    protected $setting;

    public function __construct(
        \ilMaterialListPlugin $plugin_object,
        \CaT\Plugins\MaterialList\Materials\DB $materials_db,
        \ilSetting $setting
    ) {
        $this->plugin_object = $plugin_object;
        $this->materials_db = $materials_db;
        $this->setting = $setting;
    }

    /*****************************************************
    ******************************************************
    ****************      MATERIAL     *******************
    ******************************************************
    *****************************************************/

    /**
     * Get all current available materials
     *
     * @return \CaT\Plugins\MaterialList\Materials\Material[] | []
     */
    public function getCurrentMaterials()
    {
        return $this->materials_db->selectAll();
    }

    /**
     * Get empty material objects according to entered number of new
     *
     * @return \CaT\Plugins\MaterialList\Materials\Material[] | []
     */
    public function getNewMaterial()
    {
        return new Materials\Material(-1);
    }

    /**
     * Save material entries
     *
     * @param Materials\Material 		$material
     *
     * @return null
     */
    public function updateMaterial($material)
    {
        $this->materials_db->update($material);
    }

    /**
     * Get an material object
     *
     * @param int 		$id
     * @param string 	$article_number
     * @param string 	$title
     *
     * @return Materials\Material
     */
    public function getMaterialObject($id, $article_number, $title)
    {
        return new Materials\Material($id, $article_number, $title);
    }

    /**
     * Create a new material enrty
     *
     * @param string 	$article_number
     * @param string 	$title
     *
     * @return null
     */
    public function createNewMaterial($article_number, $title)
    {
        return $this->materials_db->create($article_number, $title);
    }

    /**
     * Delete all materials
     *
     * @return null
     */
    public function deleteAllMaterials()
    {
        $this->materials_db->deleteAll();
    }

    /**
     * Delete selected materials
     *
     * @param int 	$material_id
     *
     * @return null
     */
    public function deleteMaterial($material_id)
    {
        $this->materials_db->delete($material_id);
    }

    /**
     * Save the behavior of the material in plugin objects
     *
     * @param string[]
     *
     * @return null
     */
    public function saveBehavior($post)
    {
        $behavior = $post[self::F_MATERIAL_BEHAVIOR];
        $this->setting->set(self::MATERIAL_BEHAVIOR_SETTING, $behavior);
    }

    /**
     * Get the behavior of material list
     *
     * @return int
     */
    public function getBehavior()
    {
        $behavior = $this->setting->get(self::MATERIAL_BEHAVIOR_SETTING);

        if ($behavior === false) {
            return self::MATERIAL_MODE_FREE;
        }

        return $behavior;
    }

    /**
     * Get option for autocomplete
     *
     * @param string 	$term
     *
     * @return array<string, string>
     */
    public function getAutoCompleteOptions($term)
    {
        return $this->getOptionsStartingWith($term);
    }

    /**
     * Get option by article number or title starting with
     *
     * @param string 	$term
     *
     * @return array<string, string>
     */
    protected function getOptionsStartingWith($term)
    {
        $results = array();

        foreach ($this->materials_db->getOptionsStartingWith($term) as $key => $value) {
            $val = implode(" - ", $value);
            $results[$val] = $val;
        }

        return $results;
    }

    /**
     * Check selected article number
     *
     * @param string 	$article_number
     *
     * @return boolean
     */
    public function articleNumberKnown($article_number)
    {
        return $this->materials_db->checkArticleNumber($article_number);
    }

    /**
     * Get the plugin object
     *
     * @return \ilMaterialListPlugin
     */
    public function getPlugin()
    {
        return $this->plugin_object;
    }
}
