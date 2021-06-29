<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * A objective collection is a objective that is achieved once all subobjectives are achieved.
 */
class ObjectiveCollection implements Objective
{
    protected string $label;
    protected bool $is_notable;

    /**
     * @var	Objective[]
     */
    protected array $objectives;

    public function __construct(string $label, bool $is_notable, Objective ...$objectives)
    {
        $this->label = $label;
        $this->is_notable = $is_notable;
        $this->objectives = $objectives;
    }

    /**
     * @return Objective[]
     */
    public function getObjectives() : array
    {
        return $this->objectives;
    }

    /**
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            get_class($this) .
            implode(
                array_map(
                    function ($g) {
                        return $g->getHash();
                    },
                    $this->objectives
                )
            )
        );
    }

    /**
     * @inheritdocs
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdocs
     */
    public function isNotable() : bool
    {
        return $this->is_notable;
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Environment $environment) : array
    {
        return $this->objectives;
    }

    /**
     * @inheritdocs
     */
    public function achieve(Environment $environment) : Environment
    {
        return $environment;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable(Environment $environment) : bool
    {
        return false;
    }
}
