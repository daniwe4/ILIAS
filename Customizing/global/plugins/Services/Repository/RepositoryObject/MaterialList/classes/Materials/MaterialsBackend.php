<?php
namespace CaT\Plugins\MaterialList\Materials;

use CaT\Plugins\MaterialList;

/**
 * Backend implementation for material handling
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class MaterialsBackend implements MaterialList\TableProcessing\Backend
{
    /**
     * @var ilPluginActions
     */
    protected $actions;

    public function __construct(MaterialList\ilPluginActions $actions)
    {
        $this->actions = $actions;
    }

    /**
     * Delete the object in record
     *
     * @param array
     *
     * @return null
     */
    public function delete($record)
    {
        $object = $record["object"];
        $this->actions->deleteMaterial($object->getId());
    }

    /**
     * Checks object in record if it is valid
     * If not fills key errors with values
     *
     * @param array
     *
     * @return array
     */
    public function valid($record)
    {
        $object = $record["object"];
        $old_article_number = $record["old_article_number"];

        if ($this->actions->getBehavior() === MaterialList\ilPluginActions::MATERIAL_MODE_AUTO_COMPLETE) {
            if ($object->getArticleNumber() == "" || $object->getArticleNumber() === null) {
                $record["errors"]["article_number"][] = "article_number_empty";
            }

            if (preg_match('/-/', $object->getArticleNumber())) {
                $record["errors"]["article_number"][] = "no_minus";
            }

            if ($old_article_number != $object->getArticleNumber() && $this->actions->articleNumberKnown($object->getArticleNumber())) {
                $record["errors"]["article_number"][] = "duplicated";
            }
        }

        if ($object->getTitle() == "" || $object->getTitle() === null) {
            $record["errors"]["title"][] = "title_empty";
        }

        return $record;
    }

    /**
     * Update an existing object
     *
     * @param array
     *
     * @return array
     */
    public function update($record)
    {
        $object = $record["object"];
        $this->actions->updateMaterial($object);
        $record["message"][] = "material_updated_succesfull";
        return $record;
    }

    /**
     * Creates a new object
     *
     * @param array
     *
     * @return array
     */
    public function create($record)
    {
        $object = $record["object"];
        $record["object"] = $this->actions->createNewMaterial($object->getArticleNumber(), $object->getTitle());
        $record["message"][] = "material_created_succesfull";
        return $record;
    }
}
