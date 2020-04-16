<?php
namespace CaT\Plugins\Accounting\Config\CostType;

use CaT\Plugins\Accounting\TableProcessing\Backend;

class CostTypeBackend implements Backend
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
        $obj = $record["object"];
        $this->actions->deleteCostType($obj->getId());
    }

    /**
     * @inhertidoc
     */
    public function valid($record)
    {
        $obj = $record["object"];
        if (!preg_match('/^[a-zA-Z0-9\_\-]*$/', $obj->getValue()) || $obj->getValue() === null) {
            $record["errors"]["value"][] = "export_is_not_match";
        }
        return $record;
    }

    /**
     * @inhertidoc
     */
    public function update($record)
    {
        $obj = $record["object"];
        $this->actions->updateCostType($obj);
        $record["message"][] = "update_successfull";
        return $record;
    }

    /**
     * @inhertidoc
     */
    public function create($record)
    {
        $obj = $record["object"];
        $record["object"] = $this->actions->insertCostType($obj);
        $record["message"][] = "created_successfull";
        return $record;
    }
}
