<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\Scale;

use CaT\Plugins\Accounting\TableProcessing\Backend;

class ScaleBackend implements Backend
{
    const C_SPAN_START = "span_start";
    const C_SPAN_END = "span_end";
    const C_PERCENT = "percent";

    /**
     * @var DB
     */
    protected $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function delete($record)
    {
        /**
         * @var Scale $scale
         */
        $scale = $record["object"];
        $this->db->delete($scale->getId());
    }

    /**
     * @inheritDoc
     */
    public function valid($record)
    {
        /**
         * @var Scale $scale
         */
        $scale = $record["object"];

        if ($scale->getSpanStart() == -1) {
            $record["errors"][self::C_SPAN_START][] = "no_span_selected";
        }

        if ($scale->getSpanEnd() == -1) {
            $record["errors"][self::C_SPAN_END][] = "no_span_selected";
        }

        if ($scale->getPercent() == -1) {
            $record["errors"][self::C_PERCENT][] = "no_span_selected";
        }

        return $record;
    }

    /**
     * @inheritDoc
     */
    public function update($record)
    {
        /**
         * @var Scale $scale
         */
        $scale = $record["object"];
        $this->db->update($scale);
        $record["message"][] = "update_successfull";
        return $record;
    }

    /**
     * @inheritDoc
     */
    public function create($record)
    {
        /**
         * @var Scale $scale
         */
        $scale = $record["object"];
        $record["object"] = $this->db->addScale($scale->getSpanStart(), $scale->getSpanEnd(), $scale->getPercent());
        $record["message"][] = "created_successfull";
        return $record;
    }
}
