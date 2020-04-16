<?php

namespace CaT\Plugins\MaterialList\Materials;

/**
 * Interface for DB handle of material values
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Install tables and standard contents and types.
     */
    public function install();

    /**
     * Update settings of an existing object.
     *
     * @param Material 	$material
     *
     * @return null
     */
    public function update(Material $material);

    /**
     * Create a new settings object for material.
     *
     * @param string 	$article_number
     * @param string 	$title
     *
     * @return \CaT\Plugins\MaterialList\Materials\Material
     */
    public function create($article_number, $title);

    /**
     * Return all defined material
     *
     * @return \CaT\Plugins\MaterialList\Materials\Material[]
     */
    public function selectAll();

    /**
     * Delete all information of the given id
     *
     * @param 	int 	$id
     *
     * @return null
     */
    public function delete($id);

    /**
     * Delete all materials
     *
     * @return null
     */
    public function deleteAll();
}
