<?php

namespace CaT\Plugins\CourseClassification\Options;

/**
 * Base interface for all options of an course classification
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(string $caption) : Option;

    public function update(Option $option);

    /**
     * @return Option[]
     */
    public function select() : array;

    public function delete(int $id);
}
