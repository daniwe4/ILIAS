<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

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


    public function __construct($crs_id, $provider_id)
    {
        assert('is_int($crs_id)');
        assert('is_int($provider_id)');

        $this->crs_id = $crs_id;
        $this->provider_id = $provider_id;
    }

    /**
     * @inheritdoc
     */
    public function getCrsId()
    {
        return $this->crs_id;
    }

    /**
     * @inheritdoc
     */
    public function isListAssignment()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCustomAssignment()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getProviderId()
    {
        return $this->provider_id;
    }

    /**
     * @inheritdoc
     */
    public function withProviderId($provider_id)
    {
        assert('is_int($provider_id)');
        $clone = clone $this;
        $clone->provider_id = $provider_id;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getProviderText()
    {
        throw new \LogicException("This is a ListAssignment. No provider-text in here.");
    }

    /**
     * @inheritdoc
     */
    public function withProviderText($text)
    {
        throw new \LogicException("This is a ListAssignment. No provider-text in here.");
    }
}
