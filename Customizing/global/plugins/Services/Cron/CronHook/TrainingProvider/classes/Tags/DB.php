<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Tags;

/**
 * Interface for tag database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function install() : void;
    public function create(string $name, string $color) : Tag;
    public function select(int $id) : Tag;
    public function update(Tag $tag) : void;
    public function delete(int $id) : void;
}
