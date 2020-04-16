<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Tags;

/**
 * Interface for tag database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
interface DB
{

    /**
     * Install $this->plugin needed object like tables or sequences
     */
    public function install();

    /**
     * Create a new tag. Object and DB
     *
     * @param string 		$name
     * @param string 		$color
     *
     * @return \CaT\Plugins\TrainingProvider\Tags\Tag
     */
    public function create($name, $color);

    /**
     * Get a tag object
     *
     * @param int 			$id
     *
     * @return \CaT\Plugins\TrainingProvider\Tags\Tag
     */
    public function select($id);

    /**
     * Update a tag
     *
     * @param \CaT\Plugins\TrainingProvider\Tags\Tag 		$tag
     */
    public function update(\CaT\Plugins\TrainingProvider\Tags\Tag $tag);

    /**
     * Delete a tag
     *
     * @param int 			$id
     */
    public function delete($id);
}
