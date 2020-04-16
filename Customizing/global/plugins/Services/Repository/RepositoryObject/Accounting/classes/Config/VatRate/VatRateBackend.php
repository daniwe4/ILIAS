<?php
namespace CaT\Plugins\Accounting\Config\VatRate;

use CaT\Plugins\Accounting\TableProcessing\Backend;

class VatRateBackend implements Backend
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
        $this->actions->deleteVatRate($obj->getId());
    }

    /**
     * @inhertidoc
     */
    public function valid($record)
    {
        $obj = $record["object"];
        if (!preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $obj->getValue()) || $obj->getValue() === null || $obj->getValue() === "") {
            $record["errors"]["value"][] = "value_float_not_match";
        }

        if ($obj->getLabel() === null || $obj->getLabel() === "") {
            $record["errors"]["label"][] = "value_no_label";
        }
        return $record;
    }

    /**
     * @inhertidoc
     */
    public function update($record)
    {
        $obj = $record["object"];
        $this->actions->updateVatRate($obj);
        $record["message"][] = "update_successfull";
        return $record;
    }

    /**
     * @inhertidoc
     */
    public function create($record)
    {
        $obj = $record["object"];
        $record["object"] = $this->actions->insertVatRate($obj);
        $record["message"][] = "created_successfull";
        return $record;
    }
}
