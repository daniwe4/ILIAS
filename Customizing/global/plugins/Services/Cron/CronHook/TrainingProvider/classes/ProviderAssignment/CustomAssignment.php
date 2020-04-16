<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

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


    public function __construct($crs_id, $text)
    {
        assert('is_int($crs_id)');
        assert('is_string($text)');

        $this->crs_id = $crs_id;
        $this->text = $text;
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
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isCustomAssignment()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getProviderId()
    {
        throw new \LogicException("This is a CustomAssignment. No provider-id in here.");
    }

    /**
     * @inheritdoc
     */
    public function withProviderId($id)
    {
        throw new \LogicException("This is a CustomAssignment. No provider-id in here.");
    }

    /**
     * @inheritdoc
     */
    public function getProviderText()
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function withProviderText($text)
    {
        assert('is_string($text)');
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }
}
