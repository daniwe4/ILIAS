<?php

namespace CaT\Plugins\RoomSetup\ServiceOptions;

use CaT\Plugins\RoomSetup\TableProcessing\Backend;

class ServiceOptionBackend implements Backend
{
    /**
     * @var
     */
    protected $actions;

    public function __construct($actions)
    {
        $this->actions = $actions;
    }
    /**
     * @inhertidoc
     */
    public function delete($record)
    {
        $service_option = $record["object"];
        $this->actions->deleteServiceOptionById($service_option->getId());
    }

    /**
     * @inhertidoc
     */
    public function valid($record)
    {
        $service_option = $record["object"];
        if ($service_option->getName() == "" || $service_option->getName() === null) {
            $record["errors"]["name"][] = "name_empty";
        }
        return $record;
    }

    /**
     * @inhertidoc
     */
    public function update($record)
    {
        $service_option = $record["object"];
        $this->actions->updateServiceOption($service_option);
        $record["message"][] = "update_successfull";
        return $record;
    }

    /**
     * @inhertidoc
     */
    public function create($record)
    {
        $service_option = $record["object"];
        $record["object"] = $this->actions->createServiceOption($service_option->getName(), $service_option->getActive());
        $record["message"][] = "created_successfull";
        return $record;
    }
}
