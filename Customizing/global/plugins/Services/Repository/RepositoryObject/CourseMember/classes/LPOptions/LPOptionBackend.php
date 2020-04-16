<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\LPOptions;

use CaT\Plugins\CourseMember\TableProcessing\backend;

/**
 * Basic implementation of the backend for any kind of object
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class LPOptionBackend implements backend
{
    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct(ilActions $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @inheritdoc
     */
    public function delete(array $record)
    {
        $object = $record["object"];
        $this->actions->delete($object->getId());
    }

    /**
     * @inheritdoc
     */
    public function valid(array $record)
    {
        $object = $record["object"];
        if ($object->getTitle() == "" || $object->getTitle() === null) {
            $record["errors"]["title"][] = "name_empty";
        }
        if ($object->getILIASLP() == -1) {
            $record["errors"]["ilias_lp"][] = "no_lp_selected";
        }

        return $record;
    }

    /**
     * @inheritdoc
     */
    public function update(array $record)
    {
        $object = $record["object"];
        $this->actions->update($object);
        $record["message"][] = "update_succesfull";
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function create(array $record)
    {
        $object = $record["object"];
        $record["object"] = $this->actions->create($object->getTitle(), $object->getILIASLP(), $object->getActive(), $object->isStandard());
        $record["message"][] = "created_succesfull";
        return $record;
    }
}
