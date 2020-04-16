<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

/**
 * Relates a text as provider to a course.
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class CustomAssignment implements ProviderAssignment
{

    /**
     * @var int
     */
    protected $crs_id;

    /**
     * @var string
     */
    protected $text;

    public function __construct(int $crs_id, string $text)
    {
        $this->crs_id = $crs_id;
        $this->text = $text;
    }

    /**
     * @inheritdoc
     */
    public function getCrsId() : int
    {
        return $this->crs_id;
    }

    /**
     * @inheritdoc
     */
    public function isListAssignment() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isCustomAssignment() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getProviderId() : int
    {
        throw new \LogicException("This is a CustomAssignment. No provider-id in here.");
    }

    /**
     * @inheritdoc
     */
    public function withProviderId(int $id) : ProviderAssignment
    {
        throw new \LogicException("This is a CustomAssignment. No provider-id in here.");
    }

    /**
     * @inheritdoc
     */
    public function getProviderText() : string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function withProviderText(string $text) : ProviderAssignment
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }
}
