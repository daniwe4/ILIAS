<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

/**
 * Relates a provider from the list to a course.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ListAssignment implements ProviderAssignment
{

    /**
     * @var int
     */
    protected $crs_id;

    /**
     * @var string
     */
    protected $provider_id;

    public function __construct(int $crs_id, int $provider_id)
    {
        $this->crs_id = $crs_id;
        $this->provider_id = $provider_id;
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCustomAssignment() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getProviderId() : int
    {
        return $this->provider_id;
    }

    /**
     * @inheritdoc
     */
    public function withProviderId(int $provider_id) : ProviderAssignment
    {
        $clone = clone $this;
        $clone->provider_id = $provider_id;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getProviderText() : string
    {
        throw new \LogicException("This is a ListAssignment. No provider-text in here.");
    }

    /**
     * @inheritdoc
     */
    public function withProviderText(string $text) : ProviderAssignment
    {
        throw new \LogicException("This is a ListAssignment. No provider-text in here.");
    }
}
